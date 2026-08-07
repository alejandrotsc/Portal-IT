<?php

namespace App\Http\Controllers;

use App\Models\EmailDelivery;
use App\Models\Incidencia;
use App\Models\Memorando;
use App\Models\Solicitud;
use Illuminate\Http\JsonResponse;

/*
|--------------------------------------------------------------------------
| Controlador de entregas de correo
|--------------------------------------------------------------------------
|
| Permite consultar el estado de una entrega de correo asociada a una gestión
| del Portal TI, verificando primero que el usuario tenga autorización para
| acceder a dicha información.
|
*/

class EmailDeliveryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Consultar estado del correo
    |--------------------------------------------------------------------------
    |
    | Recupera el estado actual de la entrega y devuelve una respuesta
    | normalizada con indicadores de cola, envío, fallo e intentos realizados.
    |
    */

    public function status(
        EmailDelivery $emailDelivery
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Usuario autenticado
        |--------------------------------------------------------------------------
        |
        | La consulta requiere una sesión válida antes de evaluar permisos
        | sobre la gestión relacionada.
        |
        */

        $usuario = auth()->user();

        abort_unless(
            $usuario !== null,
            401
        );

        /*
        |--------------------------------------------------------------------------
        | Cargar la gestión relacionada
        |--------------------------------------------------------------------------
        |
        | Recupera mediante la relación polimórfica el registro que originó
        | la entrega de correo.
        |
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
        |
        | Convierte el estado almacenado a minúsculas y elimina espacios para
        | mantener una comparación consistente en la respuesta JSON.
        |
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
        |
        | Devuelve el estado actual de la entrega junto con marcas booleanas
        | y fechas relevantes para que el frontend pueda actualizar la interfaz.
        |
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
    |
    | Determina si el usuario autenticado es propietario de la gestión que
    | originó el envío, según el tipo concreto del modelo relacionado.
    |
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