<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudMail;
use App\Models\Solicitud;
use App\Services\Mail\TrackedMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitudController extends Controller
{
    public function create()
    {
        return view(
            'solicitudes.create'
        );
    }

    public function store(
        Request $request,
        TrackedMailService $trackedMail
    ) {
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

        $ultima =
            Solicitud::query()
                ->latest('id')
                ->first();

        $numero = $ultima
            ? ((int) substr(
                $ultima->folio,
                4
            )) + 1
            : 1;

        $folio =
            'SOL-'.str_pad(
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
                'pendiente',

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
        |
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
        | Mantener compatibilidad con solicitudes
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
        | Redirección
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

    public function misSolicitudes(
        Request $request
    ) {
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

        $aniosDisponibles =
            Solicitud::query()
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

            $aniosDisponibles =
                $aniosDisponibles
                    ->sortDesc()
                    ->values();
        }

        $solicitudes =
            Solicitud::query()
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

    public function show(
        Solicitud $solicitud
    ) {
        abort_unless(
            (int) $solicitud->usuario_id
                ===
            (int) Auth::id(),
            403,
            'No tienes permiso para consultar esta solicitud.'
        );

        return view(
            'solicitudes.show',
            compact('solicitud')
        );
    }
}