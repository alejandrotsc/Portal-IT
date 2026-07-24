<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudMail;
use App\Models\Solicitud;
use App\Services\Mail\TrackedMailService;
use App\Notifications\EstadoSolicitudActualizadoNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Formulario para el usuario
    |--------------------------------------------------------------------------
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
    */

    public function store(
        Request $request,
        TrackedMailService $trackedMail
    ): RedirectResponse {
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
        | Si el SMTP falla, la solicitud permanece registrada y el error
        | queda almacenado en email_deliveries.
        |--------------------------------------------------------------------------
        */

        $delivery = $trackedMail->send(
            emailable:
                $solicitud,

            mailable:
                new SolicitudMail(
                    $solicitud
                ),

            recipientEmail:
                'alejandrotsc01@gmail.com',

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


        $emailSent =
            $delivery->fueEnviado();


        /*
        |--------------------------------------------------------------------------
        | Guardar estado del correo
        |--------------------------------------------------------------------------
        */

        $solicitud->update([
            'correo_enviado' =>
                $emailSent,

            'correo_enviado_at' =>
                $emailSent
                    ? $delivery->sent_at
                    : null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Regresar al formulario
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'solicitudes.create'
            )
            ->with(
                'success',
                $emailSent
                    ? 'La solicitud fue registrada correctamente y el equipo TI fue notificado.'
                    : 'La solicitud fue registrada correctamente, pero no fue posible enviar la notificación por correo.'
            )
            ->with(
                'folio',
                $folio
            )
            ->with(
                'email_sent',
                $emailSent
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
    */

    public function misSolicitudes(
    Request $request
): View {
    /*
    |--------------------------------------------------------------------------
    | Validación de filtros
    |--------------------------------------------------------------------------
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
    | Se calcula antes de paginar para que los valores representen todas
    | las solicitudes del periodo y no únicamente la página visible.
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
    */

    $solicitudes = (clone $consultaPeriodo)
        ->latest('created_at')
        ->paginate(10)
        ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | Vista
    |--------------------------------------------------------------------------
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
        | Consulta del listado
        |--------------------------------------------------------------------------
        */

        $solicitudes = Solicitud::query()
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
                Solicitud::count(),

            'pendientes' =>
                Solicitud::where(
                    'estado',
                    Solicitud::ESTADO_PENDIENTE
                )->count(),

            'finalizadas' =>
                Solicitud::where(
                    'estado',
                    Solicitud::ESTADO_FINALIZADA
                )->count(),

            'canceladas' =>
                Solicitud::where(
                    'estado',
                    Solicitud::ESTADO_CANCELADA
                )->count(),
        ];


        return view(
            'administracion.solicitudes.index',
            compact(
                'solicitudes',
                'categorias',
                'resumen',
                'busqueda',
                'estadoSeleccionado',
                'categoriaSeleccionada'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Detalle administrativo
    |--------------------------------------------------------------------------
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
    | Notificar actualización de estado
    |--------------------------------------------------------------------------
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