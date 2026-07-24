<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudMail;
use App\Models\Solicitud;
use App\Services\Mail\TrackedMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $request->validate([
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


        $mes = (int) $request->input(
            'mes',
            now()->month
        );

        $anio = (int) $request->input(
            'anio',
            now()->year
        );


        $aniosDisponibles = Solicitud::query()
            ->where(
                'usuario_id',
                Auth::id()
            )
            ->whereNotNull(
                'created_at'
            )
            ->selectRaw(
                'EXTRACT(YEAR FROM created_at)::int AS anio'
            )
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio');


        if (
            ! $aniosDisponibles->contains(
                now()->year
            )
        ) {
            $aniosDisponibles->push(
                now()->year
            );

            $aniosDisponibles = $aniosDisponibles
                ->sortDesc()
                ->values();
        }


        $solicitudes = Solicitud::query()
            ->where(
                'usuario_id',
                Auth::id()
            )
            ->whereMonth(
                'created_at',
                $mes
            )
            ->whereYear(
                'created_at',
                $anio
            )
            ->latest()
            ->get();


        return view(
            'solicitudes.mis-solicitudes',
            compact(
                'solicitudes',
                'mes',
                'anio',
                'aniosDisponibles'
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


        return back()->with(
            'success',
            'La solicitud fue cancelada.'
        );
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