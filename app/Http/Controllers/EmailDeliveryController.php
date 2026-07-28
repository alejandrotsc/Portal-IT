<?php

namespace App\Http\Controllers;

use App\Models\EmailDelivery;
use App\Models\Incidencia;
use App\Models\Memorando;
use App\Models\Solicitud;
use Illuminate\Http\JsonResponse;

class EmailDeliveryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Consultar estado del correo
    |--------------------------------------------------------------------------
    */

    public function status(
        EmailDelivery $emailDelivery
    ): JsonResponse {
        $usuario = auth()->user();

        abort_unless(
            $usuario !== null,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Cargar la gestión relacionada
        |--------------------------------------------------------------------------
        */

        $emailDelivery->loadMissing(
            'emailable'
        );

        $gestion =
            $emailDelivery->emailable;


        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        |
        | El estado solamente puede ser consultado por:
        |
        | - El propietario de la incidencia.
        | - El propietario de la solicitud.
        | - El solicitante del memorando.
        | - Un administrador.
        |
        */

        $esAdministrador =
            $usuario->rol?->nombre
            ===
            'Administrador';

        $esPropietario =
            $this->usuarioEsPropietario(
                $gestion,
                (int) $usuario->id
            );

        abort_unless(
            $esAdministrador
            || $esPropietario,
            403,
            'No tienes permiso para consultar el estado de este correo.'
        );


        /*
        |--------------------------------------------------------------------------
        | Estado normalizado
        |--------------------------------------------------------------------------
        */

        $estado =
            mb_strtolower(
                trim(
                    (string) $emailDelivery->status
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Respuesta
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'email' => [
                'delivery_id' =>
                    $emailDelivery->id,

                'status' =>
                    $estado,

                'queued' =>
                    in_array(
                        $estado,
                        [
                            'pendiente',
                            'enviando',
                        ],
                        true
                    ),

                'sent' =>
                    $estado === 'enviado',

                'failed' =>
                    $estado === 'fallido',

                'attempts' =>
                    (int) $emailDelivery->attempts,

                'queued_at' =>
                    $emailDelivery->queued_at
                        ?->toISOString(),

                'last_attempt_at' =>
                    $emailDelivery->last_attempt_at
                        ?->toISOString(),

                'sent_at' =>
                    $emailDelivery->sent_at
                        ?->toISOString(),

                'failed_at' =>
                    $emailDelivery->failed_at
                        ?->toISOString(),

                'next_retry_at' =>
                    $emailDelivery->next_retry_at
                        ?->toISOString(),
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Verificar propietario de la gestión
    |--------------------------------------------------------------------------
    */

    private function usuarioEsPropietario(
        mixed $gestion,
        int $usuarioId
    ): bool {
        return match (true) {
            $gestion instanceof Incidencia =>
                (int) $gestion->usuario_id
                ===
                $usuarioId,

            $gestion instanceof Solicitud =>
                (int) $gestion->usuario_id
                ===
                $usuarioId,

            $gestion instanceof Memorando =>
                (int) $gestion->solicitante_id
                ===
                $usuarioId,

            default =>
                false,
        };
    }
}