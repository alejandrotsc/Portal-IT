<?php

namespace App\Http\Controllers;

use App\Mail\IncidenciaMail;
use App\Models\Incidencia;
use App\Models\IncidenciaArchivo;
use App\Models\Usuario;
use App\Services\Mail\TrackedMailService;
use App\Notifications\EstadoIncidenciaActualizadoNotification;
use App\Notifications\NuevaIncidenciaNotification;
use App\Services\OcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| Controlador de incidencias
|--------------------------------------------------------------------------
|
| Gestiona el ciclo completo de incidencias del Portal TI: registro, archivos
| y OCR, correo con seguimiento, historial del usuario, administración,
| cambios de estado, prioridad y notificaciones.
|
*/

class IncidenciaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listado general
    |--------------------------------------------------------------------------
    |
    | Obtiene todas las incidencias con sus relaciones principales y las ordena desde la más reciente para el listado general.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar listado general
    |--------------------------------------------------------------------------
    |
    | Recupera incidencias con usuario y evidencias para renderizar la vista
    | general del módulo.
    |
    */

    public function index(): View
    {
        $incidencias = Incidencia::query()
            ->with([
                'usuario',
                'archivos',
            ])
            ->latest()
            ->get();

        return view(
            'incidencias.index',
            compact('incidencias')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Formulario de incidencia
    |--------------------------------------------------------------------------
    |
    | Presenta la vista utilizada por el usuario para registrar una nueva incidencia.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar formulario de registro
    |--------------------------------------------------------------------------
    |
    | Renderiza la pantalla utilizada para reportar una nueva incidencia.
    |
    */

    public function create(): View
    {
        return view(
            'incidencias.create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar incidencia
    |--------------------------------------------------------------------------
    |
    | Valida la solicitud, genera el código, registra la incidencia, procesa evidencias con OCR, envía el correo y notifica al equipo administrativo.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Registrar incidencia
    |--------------------------------------------------------------------------
    |
    | Ejecuta el flujo completo de creación, evidencias, OCR, correo y
    | notificaciones internas.
    |
    */

    public function store(
        Request $request,
        OcrService $ocr,
        TrackedMailService $trackedMail
    ): JsonResponse {
        $validated = $request->validate([
            'titulo' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'required',
                'string',
            ],

            'tiempo_problema' => [
                'nullable',
                'string',
            ],

            'afectacion' => [
                'nullable',
                'string',
            ],

            'equipo' => [
                'nullable',
                'string',
            ],

            'ubicacion' => [
                'nullable',
                'string',
            ],

            'archivos' => [
                'nullable',
                'array',
            ],

            'archivos.*' => [
                'image',
                'max:10240',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generar código
        |--------------------------------------------------------------------------
        */

        $ultima = Incidencia::query()
            ->orderByDesc('id')
            ->first();

        $numero = $ultima
            ? ((int) substr(
                $ultima->codigo,
                4
            )) + 1
            : 1;

        $codigo = 'INC-'.str_pad(
            (string) $numero,
            5,
            '0',
            STR_PAD_LEFT
        );

        /*
        |--------------------------------------------------------------------------
        | Crear incidencia
        |--------------------------------------------------------------------------
        */

        $incidencia = Incidencia::create([
            'codigo' =>
                $codigo,

            'usuario_id' =>
                Auth::id(),

            'titulo' =>
                $validated['titulo'],

            'descripcion' =>
                $validated['descripcion'],

            'tiempo_problema' =>
                $validated['tiempo_problema']
                ?? null,

            'afectacion' =>
                $validated['afectacion']
                ?? null,

            'equipo' =>
                $validated['equipo']
                ?? null,

            'ubicacion' =>
                $validated['ubicacion']
                ?? null,

            'estado' =>
                Incidencia::ESTADO_ABIERTA,

            'prioridad' =>
                Incidencia::PRIORIDAD_MEDIA,

            'correo_enviado' =>
                false,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Guardar archivos y ejecutar OCR
        |--------------------------------------------------------------------------
        */

        $textoOCR = [];

        if ($request->hasFile('archivos')) {
            foreach (
                $request->file('archivos')
                as $archivo
            ) {
                $ruta = $archivo->store(
                    'incidencias',
                    'public'
                );

                $texto = $ocr->leerImagen(
                    storage_path(
                        'app/public/'.$ruta
                    )
                );

                if (
                    is_string($texto)
                    && trim($texto) !== ''
                ) {
                    $textoOCR[] = $texto;
                }

                IncidenciaArchivo::create([
                    'incidencia_id' =>
                        $incidencia->id,

                    'usuario_id' =>
                        Auth::id(),

                    'nombre_original' =>
                        $archivo->getClientOriginalName(),

                    'nombre_archivo' =>
                        basename($ruta),

                    'ruta' =>
                        $ruta,

                    'extension' =>
                        $archivo->getClientOriginalExtension(),

                    'tamano' =>
                        $archivo->getSize(),

                    'texto_ocr' =>
                        $texto ?: null,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Enviar correo con seguimiento
        |--------------------------------------------------------------------------
        */

        $delivery = $trackedMail->sendAsync(
            emailable:
                $incidencia,

            mailable:
                new IncidenciaMail(
                    $incidencia
                ),

            recipientEmail:
                'helpdesk@televicentro.hn',

            mailType:
                'incidencia_creada',

            recipientName:
                'Equipo de soporte TI',

            subject:
                'Nueva incidencia '.$incidencia->codigo,

            metadata: [
                'codigo' =>
                    $incidencia->codigo,

                'usuario_id' =>
                    Auth::id(),

                'cantidad_archivos' =>
                    $incidencia
                        ->archivos()
                        ->count(),

                'cantidad_textos_ocr' =>
                    count($textoOCR),
            ]
        );

        /*
         * El correo todavía no ha sido enviado.
         * SendTrackedMailJob actualizará estos campos
         * cuando el servidor SMTP confirme el envío.
         */
        $incidencia->update([
            'correo_enviado' =>
                false,

            'fecha_envio_correo' =>
                null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Notificar nueva incidencia a los administradores
        |--------------------------------------------------------------------------
        */

        $this->notificarNuevaIncidencia(
            $incidencia
        );

        return response()->json([
            'success' =>
                true,

            'registered' =>
                true,

            'codigo' =>
                $incidencia->codigo,

            'email' => [
                'sent' =>
                    false,

                'queued' =>
                    $delivery->estaPendiente(),

                'status' =>
                    $delivery->status,

                'delivery_id' =>
                    $delivery->id,
            ],

            'message' =>
                $delivery->estaPendiente()
                    ? 'El reporte de incidencia fue registrado correctamente. La notificación por correo se está procesando.'
                    : 'El reporte de incidencia fue registrado correctamente, pero no fue posible colocar la notificación en la cola de correo.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Incidencias del usuario
    |--------------------------------------------------------------------------
    |
    | Construye el historial mensual del usuario autenticado junto con indicadores, años disponibles y paginación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar historial del usuario
    |--------------------------------------------------------------------------
    |
    | Valida el período seleccionado y prepara incidencias, métricas y años
    | disponibles correspondientes al usuario autenticado.
    |
    */

    public function misIncidencias(
        Request $request
    ): View {
        $validated = $request->validate([
            'mes' => [
                'nullable',
                'integer',
                'between:1,12',
            ],

            'anio' => [
                'nullable',
                'integer',
                'min:2020',
                'max:'.now()->year,
            ],
        ]);

        $mes = (int) (
            $validated['mes']
            ?? now()->month
        );

        $anio = (int) (
            $validated['anio']
            ?? now()->year
        );

        $usuarioId =
            (int) Auth::id();

        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $aniosDisponibles = Incidencia::query()
            ->where(
                'usuario_id',
                $usuarioId
            )
            ->selectRaw(
                'DISTINCT EXTRACT(YEAR FROM created_at)::integer AS anio'
            )
            ->orderByDesc('anio')
            ->pluck('anio')
            ->map(
                static fn ($valor): int =>
                    (int) $valor
            )
            ->push(
                now()->year
            )
            ->unique()
            ->sortDesc()
            ->values();

        $consultaPeriodo = Incidencia::query()
            ->where(
                'usuario_id',
                $usuarioId
            )
            ->whereYear(
                'created_at',
                $anio
            )
            ->whereMonth(
                'created_at',
                $mes
            );

        $totalIncidencias =
            (clone $consultaPeriodo)
                ->count();

        $totalEvidencias = IncidenciaArchivo::query()
            ->whereIn(
                'incidencia_id',
                (clone $consultaPeriodo)
                    ->select('id')
            )
            ->count();

        $ultimaIncidencia =
            (clone $consultaPeriodo)
                ->latest('created_at')
                ->first();

        $incidencias =
            (clone $consultaPeriodo)
                ->with('archivos')
                ->latest('created_at')
                ->paginate(8)
                ->withQueryString();

        return view(
            'incidencias.mis-incidencias',
            compact(
                'incidencias',
                'mes',
                'anio',
                'meses',
                'aniosDisponibles',
                'totalIncidencias',
                'totalEvidencias',
                'ultimaIncidencia'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Detalle para el usuario
    |--------------------------------------------------------------------------
    |
    | Permite consultar una incidencia únicamente cuando pertenece al usuario autenticado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar detalle propio
    |--------------------------------------------------------------------------
    |
    | Verifica propiedad del registro antes de cargar y mostrar la incidencia.
    |
    */

    public function show(
        Incidencia $incidencia
    ): View {
        abort_unless(
            (int) $incidencia->usuario_id
                === (int) Auth::id(),
            403,
            'No tienes permiso para consultar esta incidencia.'
        );

        $incidencia->load([
            'usuario',
            'archivos',
        ]);

        return view(
            'incidencias.show',
            compact('incidencia')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Listado administrativo
    |--------------------------------------------------------------------------
    |
    | Prepara el panel de gestión con filtros por período, búsqueda, estado, prioridad y métricas del mes seleccionado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar administración de incidencias
    |--------------------------------------------------------------------------
    |
    | Valida filtros y construye el listado administrativo junto con el resumen
    | de estados del período seleccionado.
    |
    */

    public function administracion(
        Request $request
    ): View {
        $validated = $request->validate([
            'mes' => [
                'nullable',
                'integer',
                'between:1,12',
            ],

            'anio' => [
                'nullable',
                'integer',
                'min:2020',
                'max:'.now()->year,
            ],

            'buscar' => [
                'nullable',
                'string',
                'max:200',
            ],

            'estado' => [
                'nullable',
                Rule::in(
                    Incidencia::ESTADOS
                ),
            ],

            'prioridad' => [
                'nullable',
                Rule::in(
                    Incidencia::PRIORIDADES
                ),
            ],
        ]);

        $mes = (int) (
            $validated['mes']
            ?? now()->month
        );

        $anio = (int) (
            $validated['anio']
            ?? now()->year
        );

        $busqueda = trim(
            (string) (
                $validated['buscar']
                ?? ''
            )
        );

        $estadoSeleccionado =
            $validated['estado']
            ?? null;

        $prioridadSeleccionada =
            $validated['prioridad']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Consulta base y años disponibles
        |--------------------------------------------------------------------------
        */

        $consultaBase = Incidencia::query();

        $aniosDisponibles = (clone $consultaBase)
            ->whereNotNull(
                'created_at'
            )
            ->selectRaw(
                'DISTINCT EXTRACT(YEAR FROM created_at)::integer AS anio'
            )
            ->orderByDesc('anio')
            ->pluck('anio')
            ->map(
                static fn ($valor): int =>
                    (int) $valor
            )
            ->push(
                now()->year
            )
            ->unique()
            ->sortDesc()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Consulta del periodo seleccionado
        |--------------------------------------------------------------------------
        */

        $consultaPeriodo = (clone $consultaBase)
            ->whereYear(
                'created_at',
                $anio
            )
            ->whereMonth(
                'created_at',
                $mes
            );

        $incidencias = (clone $consultaPeriodo)
            ->with([
                'usuario',
                'archivos',
            ])
            ->when(
                $busqueda !== '',
                function ($query) use ($busqueda) {
                    $termino = mb_strtolower(
                        $busqueda
                    );

                    $query->where(
                        function ($subquery) use ($termino) {
                            $subquery
                                ->whereRaw(
                                    'LOWER(codigo) LIKE ?',
                                    ["%{$termino}%"]
                                )
                                ->orWhereRaw(
                                    'LOWER(titulo) LIKE ?',
                                    ["%{$termino}%"]
                                )
                                ->orWhereHas(
                                    'usuario',
                                    function ($usuarioQuery) use ($termino) {
                                        $usuarioQuery
                                            ->whereRaw(
                                                'LOWER(nombre) LIKE ?',
                                                ["%{$termino}%"]
                                            )
                                            ->orWhereRaw(
                                                'LOWER(correo) LIKE ?',
                                                ["%{$termino}%"]
                                            );
                                    }
                                );
                        }
                    );
                }
            )
            ->when(
                filled($estadoSeleccionado),
                fn ($query) => $query->where(
                    'estado',
                    $estadoSeleccionado
                )
            )
            ->when(
                filled($prioridadSeleccionada),
                fn ($query) => $query->where(
                    'prioridad',
                    $prioridadSeleccionada
                )
            )
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $resumen = [
            'total' =>
                (clone $consultaPeriodo)
                    ->count(),

            'abiertas' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Incidencia::ESTADO_ABIERTA
                    )
                    ->count(),

            'en_proceso' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Incidencia::ESTADO_EN_PROCESO
                    )
                    ->count(),

            'resueltas' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Incidencia::ESTADO_RESUELTA
                    )
                    ->count(),
        ];

        return view(
            'administracion.incidencias.index',
            compact(
                'incidencias',
                'resumen',
                'busqueda',
                'estadoSeleccionado',
                'prioridadSeleccionada',
                'mes',
                'anio',
                'aniosDisponibles'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Detalle administrativo
    |--------------------------------------------------------------------------
    |
    | Carga la incidencia con usuario y evidencias para mostrar su información completa en el panel administrativo.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar detalle administrativo
    |--------------------------------------------------------------------------
    |
    | Carga las relaciones principales de la incidencia para su revisión en el
    | panel administrativo.
    |
    */

    public function showAdministracion(
    Incidencia $incidencia
): View {
    $incidencia->load([
        'usuario',
        'archivos',
    ]);

    return view(
        'administracion.incidencias.show',
        compact('incidencia')
    );
}

    /*
    |--------------------------------------------------------------------------
    | Iniciar atención
    |--------------------------------------------------------------------------
    |
    | Cambia una incidencia abierta a estado en proceso y notifica al usuario sobre la actualización.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Iniciar atención de incidencia
    |--------------------------------------------------------------------------
    |
    | Cambia el estado a en proceso cuando corresponde y evita transiciones
    | innecesarias o inválidas.
    |
    */

    public function iniciar(
        Incidencia $incidencia
    ): RedirectResponse {
        if ($incidencia->estaResuelta()) {
            return back()->withErrors([
                'incidencia' =>
                    'Una incidencia resuelta debe reabrirse antes de iniciar nuevamente su atención.',
            ]);
        }

        if ($incidencia->estaEnProceso()) {
            return back()->with(
                'success',
                'La incidencia ya se encuentra en proceso.'
            );
        }

        $incidencia->update([
            'estado' =>
                Incidencia::ESTADO_EN_PROCESO,
        ]);

        $this->notificarEstadoIncidencia(
            $incidencia
        );

        return back()->with(
            'success',
            'La incidencia ahora se encuentra en proceso.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolver incidencia
    |--------------------------------------------------------------------------
    |
    | Marca la incidencia como resuelta y notifica el nuevo estado al propietario.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Marcar incidencia como resuelta
    |--------------------------------------------------------------------------
    |
    | Actualiza el estado y notifica al usuario, evitando repetir la operación
    | cuando ya se encuentra resuelta.
    |
    */

    public function resolver(
        Incidencia $incidencia
    ): RedirectResponse {
        if ($incidencia->estaResuelta()) {
            return back()->with(
                'success',
                'La incidencia ya se encuentra resuelta.'
            );
        }

        $incidencia->update([
            'estado' =>
                Incidencia::ESTADO_RESUELTA,
        ]);

        $this->notificarEstadoIncidencia(
            $incidencia
        );

        return back()->with(
            'success',
            'La incidencia fue marcada como resuelta.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reabrir incidencia
    |--------------------------------------------------------------------------
    |
    | Permite devolver una incidencia resuelta al estado abierto y registra la actualización correspondiente.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Reabrir incidencia resuelta
    |--------------------------------------------------------------------------
    |
    | Devuelve una incidencia resuelta al estado abierto y notifica al usuario.
    |
    */

    public function reabrir(
        Incidencia $incidencia
    ): RedirectResponse {
        if (! $incidencia->estaResuelta()) {
            return back()->withErrors([
                'incidencia' =>
                    'Solamente se pueden reabrir incidencias resueltas.',
            ]);
        }

        $incidencia->update([
            'estado' =>
                Incidencia::ESTADO_ABIERTA,
        ]);

        $this->notificarEstadoIncidencia(
            $incidencia
        );

        return back()->with(
            'success',
            'La incidencia fue reabierta correctamente.'
        );
    }

    /*
|--------------------------------------------------------------------------
| Notificar nueva incidencia al equipo administrativo
|--------------------------------------------------------------------------
|
| Envía la notificación a todos los administradores y usuarios TI activos,
| ya que ambos roles pueden gestionar incidencias dentro del Portal TI.
|
*/

/*
|--------------------------------------------------------------------------
| Enviar notificación administrativa
|--------------------------------------------------------------------------
|
| Selecciona administradores y usuarios TI activos y distribuye la notificación
| correspondiente a la nueva incidencia.
|
*/

private function notificarNuevaIncidencia(
    Incidencia $incidencia
): void {
    try {
        $incidencia->loadMissing(
            'usuario'
        );

        $equipoAdministrativo = Usuario::query()
            ->where(
                'activo',
                true
            )
            ->whereHas(
                'rol',
                function ($query) {
                    $query->whereIn(
                        'nombre',
                        [
                            'Administrador',
                            'UsuarioTI',
                        ]
                    );
                }
            )
            ->get();

        if ($equipoAdministrativo->isEmpty()) {
            Log::warning(
                'No existen administradores o usuarios TI activos para recibir la notificación de la nueva incidencia.',
                [
                    'incidencia_id' =>
                        $incidencia->id,

                    'codigo' =>
                        $incidencia->codigo,
                ]
            );

            return;
        }

        Notification::send(
            $equipoAdministrativo,
            new NuevaIncidenciaNotification(
                $incidencia
            )
        );
    } catch (\Throwable $exception) {
        Log::error(
            'No se pudo enviar la notificación de la nueva incidencia al equipo administrativo.',
            [
                'incidencia_id' =>
                    $incidencia->id,

                'codigo' =>
                    $incidencia->codigo,

                'usuario_id' =>
                    $incidencia->usuario_id,

                'error' =>
                    $exception->getMessage(),
            ]
        );
    }
}


    /*
    |--------------------------------------------------------------------------
    | Notificar actualización de estado
    |--------------------------------------------------------------------------
    |
    | Notifica al propietario cuando cambia el estado operativo de su incidencia.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Notificar cambio de estado
    |--------------------------------------------------------------------------
    |
    | Envía al propietario una notificación con el estado actualizado de la
    | incidencia y registra cualquier fallo en el log.
    |
    */

    private function notificarEstadoIncidencia(
        Incidencia $incidencia
    ): void {
        try {
            $incidencia->loadMissing(
                'usuario'
            );

            $incidencia->usuario?->notify(
                new EstadoIncidenciaActualizadoNotification(
                    $incidencia
                )
            );
        } catch (\Throwable $exception) {
            Log::error(
                'No se pudo registrar la notificación del cambio de estado de la incidencia.',
                [
                    'incidencia_id' =>
                        $incidencia->id,

                    'usuario_id' =>
                        $incidencia->usuario_id,

                    'estado' =>
                        $incidencia->estado,

                    'error' =>
                        $exception->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar prioridad
    |--------------------------------------------------------------------------
    |
    | Valida y modifica la prioridad asignada a una incidencia desde el panel administrativo.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Modificar prioridad
    |--------------------------------------------------------------------------
    |
    | Valida que la prioridad pertenezca al catálogo permitido y actualiza el
    | registro seleccionado.
    |
    */

    public function actualizarPrioridad(
        Request $request,
        Incidencia $incidencia
    ): RedirectResponse {
        $validated = $request->validate([
            'prioridad' => [
                'required',
                Rule::in(
                    Incidencia::PRIORIDADES
                ),
            ],
        ], [
            'prioridad.required' =>
                'Debe seleccionar una prioridad.',

            'prioridad.in' =>
                'La prioridad seleccionada no es válida.',
        ]);

        $incidencia->update([
            'prioridad' =>
                $validated['prioridad'],
        ]);

        return back()->with(
            'success',
            'La prioridad fue actualizada correctamente.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibilidad con la ruta anterior
    |--------------------------------------------------------------------------
    |
    | Mantiene la ruta histórica de cierre delegando su comportamiento al método que resuelve la incidencia.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cerrar incidencia
    |--------------------------------------------------------------------------
    |
    | Conserva compatibilidad con la ruta anterior reutilizando el flujo de
    | resolución actual.
    |
    */

    public function cerrar(
        Incidencia $incidencia
    ): RedirectResponse {
        return $this->resolver(
            $incidencia
        );
    }
}