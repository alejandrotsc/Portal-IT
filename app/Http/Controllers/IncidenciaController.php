<?php

namespace App\Http\Controllers;

use App\Mail\IncidenciaMail;
use App\Models\Incidencia;
use App\Models\IncidenciaArchivo;
use App\Services\Mail\TrackedMailService;
use App\Notifications\EstadoIncidenciaActualizadoNotification;
use App\Services\OcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IncidenciaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listado general
    |--------------------------------------------------------------------------
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

        $delivery = $trackedMail->send(
            emailable:
                $incidencia,

            mailable:
                new IncidenciaMail(
                    $incidencia,
                    $textoOCR
                ),

            recipientEmail:
                'alejandrotsc01@gmail.com',

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

        $emailSent =
            $delivery->fueEnviado();

        $incidencia->update([
            'correo_enviado' =>
                $emailSent,

            'fecha_envio_correo' =>
                $emailSent
                    ? $delivery->sent_at
                    : null,
        ]);

        return response()->json([
            'success' =>
                true,

            'registered' =>
                true,

            'codigo' =>
                $incidencia->codigo,

            'email' => [
                'sent' =>
                    $emailSent,

                'status' =>
                    $delivery->status,

                'delivery_id' =>
                    $delivery->id,
            ],

            'message' =>
                $emailSent
                    ? 'El reporte de incidencia fue registrado correctamente y el equipo TI fue notificado.'
                    : 'El reporte de incidencia fue registrado correctamente, pero no fue posible enviar la notificación por correo. El error quedó registrado para su revisión.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Incidencias del usuario
    |--------------------------------------------------------------------------
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
    */

    public function administracion(
        Request $request
    ): View {
        $validated = $request->validate([
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

        $incidencias = Incidencia::query()
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
                Incidencia::count(),

            'abiertas' =>
                Incidencia::where(
                    'estado',
                    Incidencia::ESTADO_ABIERTA
                )->count(),

            'en_proceso' =>
                Incidencia::where(
                    'estado',
                    Incidencia::ESTADO_EN_PROCESO
                )->count(),

            'resueltas' =>
                Incidencia::where(
                    'estado',
                    Incidencia::ESTADO_RESUELTA
                )->count(),
        ];

        return view(
    'administracion.incidencias.index',
    compact(
        'incidencias',
        'resumen',
        'busqueda',
        'estadoSeleccionado',
        'prioridadSeleccionada'
    )
);
    }

    /*
    |--------------------------------------------------------------------------
    | Detalle administrativo
    |--------------------------------------------------------------------------
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
    | Notificar actualización de estado
    |--------------------------------------------------------------------------
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
    */

    public function cerrar(
        Incidencia $incidencia
    ): RedirectResponse {
        return $this->resolver(
            $incidencia
        );
    }
}