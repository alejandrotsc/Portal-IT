<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudMail;
use App\Models\Solicitud;
use App\Models\Usuario;
use App\Notifications\EstadoSolicitudActualizadoNotification;
use App\Notifications\NuevaSolicitudNotification;
use App\Services\Mail\TrackedMailService;
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
| Controlador de solicitudes
|--------------------------------------------------------------------------
|
| Gestiona el ciclo de vida de las solicitudes del Portal TI: creación,
| campos dinámicos, correo con seguimiento, historial del usuario, panel
| administrativo, cambios de estado, notificaciones y permisos internos.
|
*/

class SolicitudController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Formulario para el usuario
    |--------------------------------------------------------------------------
    |
    | Presenta la vista utilizada por el usuario para registrar una nueva solicitud de servicio TI.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar formulario de solicitud
    |--------------------------------------------------------------------------
    |
    | Renderiza la pantalla utilizada para registrar una nueva solicitud TI.
    |
    */

    public function create(): View
    {
        return view(
            'solicitudes.create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Registrar solicitud
    |--------------------------------------------------------------------------
    |
    | Valida la información, genera el folio, conserva campos dinámicos, registra la solicitud y procesa correo y notificaciones.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Procesar nueva solicitud
    |--------------------------------------------------------------------------
    |
    | Ejecuta el flujo completo de validación, folio, persistencia, correo y
    | notificación administrativa.
    |
    */

    public function store(
        Request $request,
        TrackedMailService $trackedMail
    ): JsonResponse|RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'categoria' => [
                'required',
                'string',
                'max:50',
            ],

            'asunto' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'required',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Generar folio
        |--------------------------------------------------------------------------
        */

        $ultima = Solicitud::query()
            ->latest('id')
            ->first();

        $numero = $ultima
            ? (
                (int) substr(
                    $ultima->folio,
                    4
                )
            ) + 1
            : 1;

        $folio = 'SOL-'.str_pad(
            (string) $numero,
            5,
            '0',
            STR_PAD_LEFT
        );


        /*
        |--------------------------------------------------------------------------
        | Obtener datos dinámicos
        |--------------------------------------------------------------------------
        */

        $datosExtra = $request->except([
            '_token',
            '_method',
            'categoria',
            'asunto',
            'descripcion',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Crear solicitud
        |--------------------------------------------------------------------------
        */

        $solicitud = Solicitud::create([
            'folio' =>
                $folio,

            'usuario_id' =>
                Auth::id(),

            'categoria' =>
                $validated['categoria'],

            'asunto' =>
                $validated['asunto'],

            'descripcion' =>
                $validated['descripcion'],

            'datos_extra' =>
                $datosExtra ?: null,

            'estado' =>
                Solicitud::ESTADO_PENDIENTE,

            'correo_enviado' =>
                false,

            'correo_enviado_at' =>
                null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Enviar correo con seguimiento
        |--------------------------------------------------------------------------
        |
        | Coloca la notificación en la cola mediante TrackedMailService. Si el
        | SMTP falla, la solicitud permanece registrada y el detalle del error
        | queda almacenado en email_deliveries.
        |
        */

        $delivery = $trackedMail->sendAsync(
            emailable:
                $solicitud,

            mailable:
                new SolicitudMail(
                    $solicitud
                ),

            recipientEmail:
                'helpdesk@televicentro.hn',

            mailType:
                'solicitud_creada',

            recipientName:
                'Equipo de soporte TI',

            subject:
                'Nueva solicitud '.$solicitud->folio,

            metadata: [
                'folio' =>
                    $solicitud->folio,

                'usuario_id' =>
                    Auth::id(),

                'categoria' =>
                    $solicitud->categoria,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Estado inicial del correo
        |--------------------------------------------------------------------------
        |
        | La solicitud permanece inicialmente sin confirmación de envío. El Job
        | actualizará estos campos cuando el servidor SMTP confirme el resultado.
        |
        */

        $solicitud->update([
            'correo_enviado' =>
                false,

            'correo_enviado_at' =>
                null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Notificar nueva solicitud a los administradores
        |--------------------------------------------------------------------------
        */

        $this->notificarNuevaSolicitud(
            $solicitud
        );


        /*
        |--------------------------------------------------------------------------
        | Regresar al formulario
        |--------------------------------------------------------------------------
        */

        $correoPendiente =
            $delivery->estaPendiente();

        $mensaje =
            $correoPendiente
                ? 'La solicitud fue registrada correctamente. La notificación por correo se está procesando.'
                : 'La solicitud fue registrada correctamente, pero no fue posible colocar la notificación en la cola de correo.';

        /*
        |--------------------------------------------------------------------------
        | Respuesta para solicitudes AJAX / fetch
        |--------------------------------------------------------------------------
        */

        if (
            $request->expectsJson()
            || $request->ajax()
        ) {
            return response()->json([
                'success' =>
                    true,

                'message' =>
                    $mensaje,

                'id' =>
                    $solicitud->id,

                'folio' =>
                    $solicitud->folio,

                'email' => [
                    'sent' =>
                        false,

                    'queued' =>
                        $correoPendiente,

                    'failed' =>
                        $delivery->status === 'fallido',

                    'status' =>
                        $delivery->status,

                    'delivery_id' =>
                        $delivery->id,
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Respuesta tradicional como respaldo
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'solicitudes.create'
            )
            ->with(
                'success',
                $mensaje
            )
            ->with(
                'folio',
                $folio
            )
            ->with(
                'email_sent',
                false
            )
            ->with(
                'email_queued',
                $correoPendiente
            )
            ->with(
                'email_status',
                $delivery->status
            )
            ->with(
                'email_delivery_id',
                $delivery->id
            )
            ->with(
                'solicitud_categoria',
                $solicitud->categoria
            )
            ->with(
                'solicitud_asunto',
                $solicitud->asunto
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Solicitudes del usuario autenticado
    |--------------------------------------------------------------------------
    |
    | Construye el historial mensual del usuario junto con filtros, resumen, años disponibles y paginación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar historial del usuario
    |--------------------------------------------------------------------------
    |
    | Valida el período, calcula métricas y prepara el listado paginado de las
    | solicitudes pertenecientes al usuario autenticado.
    |
    */

    public function misSolicitudes(
    Request $request
): View {
    /*
    |--------------------------------------------------------------------------
    | Validación de filtros
    |--------------------------------------------------------------------------
    |
    | Valida mes y año antes de construir el historial del usuario.
    |
    */

    $validated = $request->validate([
        'mes' => [
            'nullable',
            'integer',
            'between:1,12',
        ],

        'anio' => [
            'nullable',
            'integer',
            'between:2020,'.now()->year,
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

    $usuarioId = (int) Auth::id();


    /*
    |--------------------------------------------------------------------------
    | Años disponibles
    |--------------------------------------------------------------------------
    |
    | Obtiene los años con solicitudes registradas y conserva siempre el año actual.
    |
    */

    $aniosDisponibles = Solicitud::query()
        ->where(
            'usuario_id',
            $usuarioId
        )
        ->whereNotNull(
            'created_at'
        )
        ->selectRaw(
            'EXTRACT(YEAR FROM created_at)::int AS anio'
        )
        ->distinct()
        ->orderByDesc('anio')
        ->pluck('anio')
        ->map(
            static fn ($valor): int =>
                (int) $valor
        );


    if (
        ! $aniosDisponibles->contains(
            now()->year
        )
    ) {
        $aniosDisponibles->push(
            now()->year
        );
    }


    $aniosDisponibles = $aniosDisponibles
        ->unique()
        ->sortDesc()
        ->values();


    /*
    |--------------------------------------------------------------------------
    | Consulta del periodo
    |--------------------------------------------------------------------------
    |
    | Limita las solicitudes al usuario autenticado y al mes y año seleccionados.
    |
    */

    $consultaPeriodo = Solicitud::query()
        ->where(
            'usuario_id',
            $usuarioId
        )
        ->whereMonth(
            'created_at',
            $mes
        )
        ->whereYear(
            'created_at',
            $anio
        );


    /*
    |--------------------------------------------------------------------------
    | Resumen del periodo
    |--------------------------------------------------------------------------
    |
    | Se calcula antes de paginar para que los indicadores representen todas
    | las solicitudes del período y no únicamente la página visible.
    |
    */

    $totalSolicitudes = (clone $consultaPeriodo)
        ->count();


    $solicitudesPendientes = (clone $consultaPeriodo)
        ->whereIn(
            'estado',
            [
                'pendiente',
                'en_proceso',
            ]
        )
        ->count();


    $ultimaSolicitud = (clone $consultaPeriodo)
        ->latest('created_at')
        ->first();


    /*
    |--------------------------------------------------------------------------
    | Listado paginado
    |--------------------------------------------------------------------------
    |
    | Ordena las solicitudes por fecha y prepara la paginación conservando los filtros activos.
    |
    */

    $solicitudes = (clone $consultaPeriodo)
        ->latest('created_at')
        ->paginate(10)
        ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | Vista
    |--------------------------------------------------------------------------
    |
    | Entrega a la vista todas las colecciones, filtros e indicadores calculados.
    |
    */

    return view(
        'solicitudes.mis-solicitudes',
        compact(
            'solicitudes',
            'mes',
            'anio',
            'aniosDisponibles',
            'totalSolicitudes',
            'solicitudesPendientes',
            'ultimaSolicitud'
        )
    );
}


    /*
    |--------------------------------------------------------------------------
    | Detalle para el usuario
    |--------------------------------------------------------------------------
    |
    | Permite consultar una solicitud únicamente cuando pertenece al usuario autenticado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar detalle propio
    |--------------------------------------------------------------------------
    |
    | Verifica la propiedad de la solicitud antes de presentar su detalle.
    |
    */

    public function show(
        Solicitud $solicitud
    ): View {
        abort_unless(
            (int) $solicitud->usuario_id
                === (int) Auth::id(),
            403,
            'No tienes permiso para consultar esta solicitud.'
        );


        return view(
            'solicitudes.show',
            compact('solicitud')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Listado administrativo
    |--------------------------------------------------------------------------
    |
    | Prepara el panel administrativo con filtros por período, búsqueda, estado, categoría y resumen.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar administración de solicitudes
    |--------------------------------------------------------------------------
    |
    | Valida filtros y construye el listado administrativo con búsqueda,
    | categoría, estado y métricas del período.
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
                'between:2020,'.now()->year,
            ],

            'buscar' => [
                'nullable',
                'string',
                'max:200',
            ],

            'estado' => [
                'nullable',

                Rule::in([
                    Solicitud::ESTADO_PENDIENTE,
                    Solicitud::ESTADO_FINALIZADA,
                    Solicitud::ESTADO_CANCELADA,
                ]),
            ],

            'categoria' => [
                'nullable',
                'string',
                'max:50',
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
            ?? '';

        $categoriaSeleccionada =
            $validated['categoria']
            ?? '';


        /*
        |--------------------------------------------------------------------------
        | Consulta base y años disponibles
        |--------------------------------------------------------------------------
        */

        $consultaBase = Solicitud::query();

        $aniosDisponibles = (clone $consultaBase)
            ->whereNotNull(
                'created_at'
            )
            ->selectRaw(
                'EXTRACT(YEAR FROM created_at)::int AS anio'
            )
            ->distinct()
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
            ->whereMonth(
                'created_at',
                $mes
            )
            ->whereYear(
                'created_at',
                $anio
            );


        /*
        |--------------------------------------------------------------------------
        | Consulta del listado
        |--------------------------------------------------------------------------
        */

        $solicitudes = (clone $consultaPeriodo)
            ->with('usuario')

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
                                    'LOWER(folio) LIKE ?',
                                    ["%{$termino}%"]
                                )
                                ->orWhereRaw(
                                    'LOWER(asunto) LIKE ?',
                                    ["%{$termino}%"]
                                )
                                ->orWhereRaw(
                                    'LOWER(categoria) LIKE ?',
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
                $estadoSeleccionado !== '',
                fn ($query) => $query->where(
                    'estado',
                    $estadoSeleccionado
                )
            )

            ->when(
                $categoriaSeleccionada !== '',
                fn ($query) => $query->where(
                    'categoria',
                    $categoriaSeleccionada
                )
            )

            ->latest()
            ->paginate(12)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Categorías disponibles
        |--------------------------------------------------------------------------
        */

        $categorias = Solicitud::query()
            ->whereNotNull('categoria')
            ->where('categoria', '<>', '')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');


        /*
        |--------------------------------------------------------------------------
        | Resumen
        |--------------------------------------------------------------------------
        */

        $resumen = [
            'total' =>
                (clone $consultaPeriodo)
                    ->count(),

            'pendientes' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Solicitud::ESTADO_PENDIENTE
                    )
                    ->count(),

            'finalizadas' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Solicitud::ESTADO_FINALIZADA
                    )
                    ->count(),

            'canceladas' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Solicitud::ESTADO_CANCELADA
                    )
                    ->count(),
        ];


        return view(
            'administracion.solicitudes.index',
            compact(
                'solicitudes',
                'categorias',
                'resumen',
                'busqueda',
                'estadoSeleccionado',
                'categoriaSeleccionada',
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
    | Carga el usuario relacionado y muestra la solicitud en el panel administrativo.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar detalle administrativo
    |--------------------------------------------------------------------------
    |
    | Verifica permisos internos y carga la información del usuario asociado
    | antes de mostrar la solicitud.
    |
    */

    public function showAdministracion(
        Solicitud $solicitud
    ): View {
        $this->autorizarSeguimiento();

        $solicitud->load(
            'usuario'
        );


        return view(
            'administracion.solicitudes.show',
            compact('solicitud')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Finalizar solicitud
    |--------------------------------------------------------------------------
    |
    | Cambia una solicitud pendiente a finalizada y notifica al propietario.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Finalizar solicitud pendiente
    |--------------------------------------------------------------------------
    |
    | Aplica la transición a finalizada únicamente cuando la gestión continúa
    | pendiente y notifica al usuario.
    |
    */

    public function finalizar(
        Solicitud $solicitud
    ): RedirectResponse {
        $this->autorizarSeguimiento();


        if (! $solicitud->estaPendiente()) {
            return back()->withErrors([
                'estado' =>
                    'Solo se pueden finalizar solicitudes pendientes.',
            ]);
        }


        $solicitud->update([
            'estado' =>
                Solicitud::ESTADO_FINALIZADA,
        ]);


        $this->notificarEstadoSolicitud(
            $solicitud
        );


        return back()->with(
            'success',
            'La solicitud fue marcada como finalizada.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancelar solicitud
    |--------------------------------------------------------------------------
    |
    | Cambia una solicitud pendiente a cancelada y notifica al propietario.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cancelar solicitud pendiente
    |--------------------------------------------------------------------------
    |
    | Aplica la transición a cancelada únicamente cuando la gestión continúa
    | pendiente y notifica al usuario.
    |
    */

    public function cancelar(
        Solicitud $solicitud
    ): RedirectResponse {
        $this->autorizarSeguimiento();


        if (! $solicitud->estaPendiente()) {
            return back()->withErrors([
                'estado' =>
                    'Solo se pueden cancelar solicitudes pendientes.',
            ]);
        }


        $solicitud->update([
            'estado' =>
                Solicitud::ESTADO_CANCELADA,
        ]);


        $this->notificarEstadoSolicitud(
            $solicitud
        );


        return back()->with(
            'success',
            'La solicitud fue cancelada.'
        );
    }


    /*
|--------------------------------------------------------------------------
| Notificar nueva solicitud al equipo administrativo
|--------------------------------------------------------------------------
|
| Envía la notificación a administradores y usuarios TI activos, ya que ambos
| roles pueden participar en el seguimiento de solicitudes.
|
*/

/*
|--------------------------------------------------------------------------
| Enviar notificación administrativa
|--------------------------------------------------------------------------
|
| Selecciona administradores y usuarios TI activos y distribuye la notificación
| correspondiente a la nueva solicitud.
|
*/

private function notificarNuevaSolicitud(
    Solicitud $solicitud
): void {
    try {
        $solicitud->loadMissing(
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
                'No existen administradores o usuarios TI activos para recibir la notificación de la nueva solicitud.',
                [
                    'solicitud_id' =>
                        $solicitud->id,

                    'folio' =>
                        $solicitud->folio,
                ]
            );

            return;
        }

        Notification::send(
            $equipoAdministrativo,
            new NuevaSolicitudNotification(
                $solicitud
            )
        );
    } catch (\Throwable $exception) {
        Log::error(
            'No se pudo enviar la notificación de la nueva solicitud al equipo administrativo.',
            [
                'solicitud_id' =>
                    $solicitud->id,

                'folio' =>
                    $solicitud->folio,

                'usuario_id' =>
                    $solicitud->usuario_id,

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
    | Notifica al usuario cuando cambia el estado de su solicitud.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Notificar cambio de estado
    |--------------------------------------------------------------------------
    |
    | Envía al propietario la actualización de estado y registra cualquier
    | fallo de notificación sin interrumpir la operación principal.
    |
    */

    private function notificarEstadoSolicitud(
        Solicitud $solicitud
    ): void {
        try {
            $solicitud->loadMissing(
                'usuario'
            );

            $solicitud->usuario?->notify(
                new EstadoSolicitudActualizadoNotification(
                    $solicitud
                )
            );
        } catch (\Throwable $exception) {
            Log::error(
                'No se pudo registrar la notificación del cambio de estado de la solicitud.',
                [
                    'solicitud_id' =>
                        $solicitud->id,

                    'usuario_id' =>
                        $solicitud->usuario_id,

                    'estado' =>
                        $solicitud->estado,

                    'error' =>
                        $exception->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Autorizar gestión interna
    |--------------------------------------------------------------------------
    |
    | Restringe las acciones administrativas a usuarios con rol UsuarioTI o Administrador.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Verificar permisos de seguimiento
    |--------------------------------------------------------------------------
    |
    | Confirma que el usuario autenticado tenga rol UsuarioTI o Administrador
    | antes de permitir modificaciones sobre solicitudes.
    |
    */

    private function autorizarSeguimiento(): void
    {
        $usuario = Auth::user();

        $tieneControl = in_array(
            $usuario->rol?->nombre,
            [
                'UsuarioTI',
                'Administrador',
            ],
            true
        );


        abort_unless(
            $tieneControl,
            403,
            'No tienes permiso para modificar solicitudes.'
        );
    }
}