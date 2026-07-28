<?php

namespace App\Jobs;

use App\Mail\AutorizacionMail;
use App\Mail\CodigoVerificacionMail;
use App\Mail\EnlaceMagicoMail;
use App\Mail\IncidenciaMail;
use App\Mail\PaseTemporalMail;
use App\Mail\SolicitudMail;
use App\Models\EmailDelivery;
use App\Models\Incidencia;
use App\Models\Memorando;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class SendTrackedMailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Cantidad máxima de intentos.
     */
    public int $tries = 3;

    /**
     * Tiempo máximo de ejecución por intento.
     */
    public int $timeout = 120;

    /**
     * Evita que el worker continúe ejecutando el Job
     * después de superar el tiempo límite.
     */
    public bool $failOnTimeout = true;

    /**
     * ID del registro de seguimiento del correo.
     */
    public function __construct(
        public readonly int $emailDeliveryId
    ) {
        $this->onQueue('emails');
    }

    /**
     * Tiempos de espera entre reintentos.
     */
    public function backoff(): array
    {
        return [
            30,
            120,
            300,
        ];
    }

    /**
     * Procesar el envío del correo.
     */
    public function handle(): void
    {
        $delivery = EmailDelivery::query()
            ->findOrFail(
                $this->emailDeliveryId
            );

        /*
        |--------------------------------------------------------------------------
        | Evitar reenvíos accidentales
        |--------------------------------------------------------------------------
        */

        if ($delivery->fueEnviado()) {
            Log::info(
                'El correo registrado ya había sido enviado.',
                [
                    'email_delivery_id' =>
                        $delivery->id,
                ]
            );

            return;
        }

        try {
            $delivery->marcarEnviando();

            $mailable = $this->reconstruirMailable(
                $delivery
            );

            $sentMessage = Mail::to(
                $delivery->recipient_email,
                $delivery->recipient_name
            )->send(
                $mailable
            );

            $providerMessageId = null;

            if (
                is_object($sentMessage)
                && method_exists(
                    $sentMessage,
                    'getMessageId'
                )
            ) {
                $providerMessageId =
                    $sentMessage->getMessageId();
            }

            $delivery->marcarEnviado(
                $providerMessageId
            );

            $this->marcarModeloPadreComoEnviado(
                $delivery
            );

            Log::info(
                'Correo registrado enviado correctamente.',
                [
                    'email_delivery_id' =>
                        $delivery->id,

                    'emailable_type' =>
                        $delivery->emailable_type,

                    'emailable_id' =>
                        $delivery->emailable_id,

                    'mail_type' =>
                        $delivery->mail_type,

                    'recipient_email' =>
                        $delivery->recipient_email,

                    'provider_message_id' =>
                        $providerMessageId,
                ]
            );

        } catch (Throwable $exception) {
            $intentoActual =
                max(
                    1,
                    $this->attempts()
                );

            $quedanIntentos =
                $intentoActual < $this->tries;

            $nextRetryAt =
                $quedanIntentos
                    ? $this->calcularSiguienteReintento()
                    : null;

            if ($quedanIntentos) {
                $delivery->forceFill([
                    'status' =>
                        EmailDelivery::ESTADO_PENDIENTE,

                    'next_retry_at' =>
                        $nextRetryAt,

                    'last_error' =>
                        $exception->getMessage(),

                    'last_attempt_at' =>
                        now(),
                ])->save();

            } else {
                $delivery->marcarFallido(
                    error:
                        $exception,

                    errorCode:
                        (string) $exception->getCode(),

                    nextRetryAt:
                        null
                );
            }

            Log::error(
                'Falló un intento de envío de correo registrado.',
                [
                    'email_delivery_id' =>
                        $delivery->id,

                    'emailable_type' =>
                        $delivery->emailable_type,

                    'emailable_id' =>
                        $delivery->emailable_id,

                    'mail_type' =>
                        $delivery->mail_type,

                    'recipient_email' =>
                        $delivery->recipient_email,

                    'attempt' =>
                        $intentoActual,

                    'tries' =>
                        $this->tries,

                    'will_retry' =>
                        $quedanIntentos,

                    'next_retry_at' =>
                        $nextRetryAt?->toDateTimeString(),

                    'error' =>
                        $exception->getMessage(),
                ]
            );

            throw $exception;
        }
    }

    /**
     * Reconstruir el Mailable usando la clase y metadata
     * almacenadas en EmailDelivery.
     */
    private function reconstruirMailable(
        EmailDelivery $delivery
    ): Mailable {
        $metadata =
            $delivery->metadata ?? [];

        return match (
            $delivery->mailable_class
        ) {
            IncidenciaMail::class =>
                $this->crearIncidenciaMail(
                    $metadata
                ),

            SolicitudMail::class =>
                $this->crearSolicitudMail(
                    $metadata
                ),

            PaseTemporalMail::class =>
                $this->crearPaseTemporalMail(
                    $metadata
                ),

            AutorizacionMail::class =>
                $this->crearAutorizacionMail(
                    $metadata
                ),

            EnlaceMagicoMail::class =>
                $this->crearEnlaceMagicoMail(
                    $metadata
                ),

            CodigoVerificacionMail::class =>
                $this->crearCodigoVerificacionMail(
                    $metadata
                ),

            default =>
                throw new RuntimeException(
                    'No existe un reconstructor para el Mailable: '
                    .$delivery->mailable_class
                ),
        };
    }

    /**
     * Reconstruir correo de incidencia.
     */
    private function crearIncidenciaMail(
        array $metadata
    ): IncidenciaMail {
        $incidenciaId =
            $this->obtenerIdMetadata(
                $metadata,
                'incidencia_id'
            );

        $incidencia = Incidencia::query()
            ->with([
                'usuario',
                'archivos',
            ])
            ->findOrFail(
                $incidenciaId
            );

        return new IncidenciaMail(
            $incidencia
        );
    }

    /**
     * Reconstruir correo de solicitud.
     */
    private function crearSolicitudMail(
        array $metadata
    ): SolicitudMail {
        $solicitudId =
            $this->obtenerIdMetadata(
                $metadata,
                'solicitud_id'
            );

        $solicitud = Solicitud::query()
            ->with([
                'usuario',
            ])
            ->findOrFail(
                $solicitudId
            );

        return new SolicitudMail(
            $solicitud
        );
    }

    /**
     * Reconstruir correo de pase temporal.
     */
    private function crearPaseTemporalMail(
        array $metadata
    ): PaseTemporalMail {
        $memorandoId =
            $this->obtenerIdMetadata(
                $metadata,
                'memorando_id'
            );

        $memorando = Memorando::query()
            ->with([
                'solicitante',
            ])
            ->findOrFail(
                $memorandoId
            );

        return new PaseTemporalMail(
            $memorando
        );
    }

    /**
     * Reconstruir correo de autorización.
     */
    private function crearAutorizacionMail(
        array $metadata
    ): AutorizacionMail {
        $memorandoId =
            $this->obtenerIdMetadata(
                $metadata,
                'memorando_id'
            );

        $memorando = Memorando::query()
            ->with([
                'tipo',
                'solicitante',
            ])
            ->findOrFail(
                $memorandoId
            );

        return new AutorizacionMail(
            $memorando
        );
    }

    /**
     * Reconstruir correo de enlace mágico.
     */
    private function crearEnlaceMagicoMail(
        array $metadata
    ): EnlaceMagicoMail {
        $usuarioId =
            $this->obtenerIdMetadata(
                $metadata,
                'usuario_id'
            );

        $url =
            $this->obtenerTextoMetadata(
                $metadata,
                'url',
                'URL del enlace mágico'
            );

        $usuario = Usuario::query()
            ->findOrFail(
                $usuarioId
            );

        return new EnlaceMagicoMail(
            $usuario,
            $url
        );
    }

    /**
     * Reconstruir correo de código de verificación.
     */
    private function crearCodigoVerificacionMail(
        array $metadata
    ): CodigoVerificacionMail {
        $usuarioId =
            $this->obtenerIdMetadata(
                $metadata,
                'usuario_id'
            );

        $codigo =
            $this->obtenerTextoMetadata(
                $metadata,
                'codigo',
                'código de verificación'
            );

        $usuario = Usuario::query()
            ->findOrFail(
                $usuarioId
            );

        return new CodigoVerificacionMail(
            $usuario,
            $codigo
        );
    }

    /**
     * Obtener y validar texto almacenado en metadata.
     */
    private function obtenerTextoMetadata(
        array $metadata,
        string $key,
        string $descripcion
    ): string {
        $value =
            $metadata[$key]
            ?? null;

        if (
            ! is_string($value)
            || trim($value) === ''
        ) {
            throw new RuntimeException(
                "La metadata del correo no contiene {$descripcion} válida."
            );
        }

        return trim(
            $value
        );
    }

    /**
     * Obtener y validar un ID almacenado en metadata.
     */
    private function obtenerIdMetadata(
        array $metadata,
        string $key
    ): int {
        $value =
            $metadata[$key] ?? null;

        if (
            ! is_numeric($value)
            || (int) $value <= 0
        ) {
            throw new RuntimeException(
                "La metadata del correo no contiene {$key} válido."
            );
        }

        return (int) $value;
    }

    /**
     * Actualizar el modelo relacionado después del envío.
     *
     * Se comprueban las columnas para mantener compatibilidad
     * entre Incidencia, Solicitud y Memorando.
     */
    private function marcarModeloPadreComoEnviado(
        EmailDelivery $delivery
    ): void {
        $emailable =
            $delivery->emailable;

        if (! $emailable) {
            Log::warning(
                'El correo fue enviado, pero no se encontró el modelo relacionado.',
                [
                    'email_delivery_id' =>
                        $delivery->id,

                    'emailable_type' =>
                        $delivery->emailable_type,

                    'emailable_id' =>
                        $delivery->emailable_id,
                ]
            );

            return;
        }

        $table =
            $emailable->getTable();

        $updates = [];

        if (
            Schema::hasColumn(
                $table,
                'correo_enviado'
            )
        ) {
            $updates['correo_enviado'] =
                true;
        }

        if (
            Schema::hasColumn(
                $table,
                'fecha_envio_correo'
            )
        ) {
            $updates['fecha_envio_correo'] =
                now();
        }

        if ($updates === []) {
            return;
        }

        $emailable->forceFill(
            $updates
        )->save();
    }

    /**
     * Calcular cuándo ocurrirá el siguiente intento.
     */
    private function calcularSiguienteReintento(): mixed
    {
        $attempt =
            max(
                1,
                $this->attempts()
            );

        $delays =
            $this->backoff();

        $index =
            min(
                $attempt - 1,
                count($delays) - 1
            );

        return now()->addSeconds(
            $delays[$index]
        );
    }

    /**
     * Registrar el fallo definitivo del Job.
     */
    public function failed(
        ?Throwable $exception
    ): void {
        $delivery = EmailDelivery::query()
            ->find(
                $this->emailDeliveryId
            );

        if (! $delivery) {
            Log::critical(
                'Falló definitivamente un correo, pero no existe EmailDelivery.',
                [
                    'email_delivery_id' =>
                        $this->emailDeliveryId,

                    'error' =>
                        $exception?->getMessage(),
                ]
            );

            return;
        }

        if (! $delivery->fueEnviado()) {
            $delivery->marcarFallido(
                error:
                    $exception
                    ?? 'El trabajo de correo falló definitivamente.',

                errorCode:
                    $exception
                        ? (string) $exception->getCode()
                        : null,

                nextRetryAt:
                    null
            );
        }

        Log::critical(
            'El correo registrado falló definitivamente.',
            [
                'email_delivery_id' =>
                    $delivery->id,

                'emailable_type' =>
                    $delivery->emailable_type,

                'emailable_id' =>
                    $delivery->emailable_id,

                'mail_type' =>
                    $delivery->mail_type,

                'recipient_email' =>
                    $delivery->recipient_email,

                'attempts' =>
                    $delivery->fresh()?->attempts,

                'error' =>
                    $exception?->getMessage(),
            ]
        );
    }
}