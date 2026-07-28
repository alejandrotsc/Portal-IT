<?php

namespace App\Services\Mail;

use App\Jobs\SendTrackedMailJob;
use App\Mail\AutorizacionMail;
use App\Mail\IncidenciaMail;
use App\Mail\PaseTemporalMail;
use App\Mail\SolicitudMail;
use App\Models\EmailDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class TrackedMailService
{
    /**
     * Envía un correo de forma síncrona y registra todo el proceso.
     *
     * Este método se conserva intacto para compatibilidad con
     * funcionalidades que todavía necesitan esperar el resultado SMTP.
     */
    public function send(
        Model $emailable,
        Mailable $mailable,
        string $recipientEmail,
        string $mailType,
        ?string $recipientName = null,
        ?string $subject = null,
        array $metadata = []
    ): EmailDelivery {
        $delivery = $this->crearDelivery(
            emailable:
                $emailable,

            mailable:
                $mailable,

            recipientEmail:
                $recipientEmail,

            mailType:
                $mailType,

            recipientName:
                $recipientName,

            subject:
                $subject,

            metadata:
                $metadata
        );

        try {
            $delivery->marcarEnviando();

            $sentMessage = Mail::to(
                $recipientEmail,
                $recipientName
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

        } catch (Throwable $exception) {
            $delivery->marcarFallido(
                error:
                    $exception,

                errorCode:
                    (string) $exception->getCode()
            );

            Log::error(
                'Falló el envío síncrono de un correo registrado.',
                [
                    'email_delivery_id' =>
                        $delivery->id,

                    'emailable_type' =>
                        $emailable->getMorphClass(),

                    'emailable_id' =>
                        $emailable->getKey(),

                    'mail_type' =>
                        $mailType,

                    'recipient_email' =>
                        $recipientEmail,

                    'error' =>
                        $exception->getMessage(),
                ]
            );
        }

        return $delivery->fresh();
    }

    /**
     * Registra el correo y lo despacha para enviarse en segundo plano.
     *
     * Retorna inmediatamente el EmailDelivery en estado pendiente,
     * sin esperar la respuesta del servidor SMTP.
     */
    public function sendAsync(
        Model $emailable,
        Mailable $mailable,
        string $recipientEmail,
        string $mailType,
        ?string $recipientName = null,
        ?string $subject = null,
        array $metadata = []
    ): EmailDelivery {
        $metadata = $this->prepararMetadata(
            emailable:
                $emailable,

            mailable:
                $mailable,

            metadata:
                $metadata
        );

        $delivery = $this->crearDelivery(
            emailable:
                $emailable,

            mailable:
                $mailable,

            recipientEmail:
                $recipientEmail,

            mailType:
                $mailType,

            recipientName:
                $recipientName,

            subject:
                $subject,

            metadata:
                $metadata
        );

        try {
            SendTrackedMailJob::dispatch(
                $delivery->id
            )->afterCommit();

            Log::info(
                'Correo registrado y enviado a la cola.',
                [
                    'email_delivery_id' =>
                        $delivery->id,

                    'emailable_type' =>
                        $emailable->getMorphClass(),

                    'emailable_id' =>
                        $emailable->getKey(),

                    'mail_type' =>
                        $mailType,

                    'recipient_email' =>
                        $recipientEmail,

                    'queue' =>
                        'emails',
                ]
            );

        } catch (Throwable $exception) {
            $delivery->marcarFallido(
                error:
                    $exception,

                errorCode:
                    (string) $exception->getCode()
            );

            Log::error(
                'No se pudo despachar el correo registrado a la cola.',
                [
                    'email_delivery_id' =>
                        $delivery->id,

                    'emailable_type' =>
                        $emailable->getMorphClass(),

                    'emailable_id' =>
                        $emailable->getKey(),

                    'mail_type' =>
                        $mailType,

                    'recipient_email' =>
                        $recipientEmail,

                    'error' =>
                        $exception->getMessage(),
                ]
            );
        }

        return $delivery->fresh();
    }

    /**
     * Crear el registro común de seguimiento del correo.
     */
    private function crearDelivery(
        Model $emailable,
        Mailable $mailable,
        string $recipientEmail,
        string $mailType,
        ?string $recipientName,
        ?string $subject,
        array $metadata
    ): EmailDelivery {
        return EmailDelivery::create([
            'emailable_type' =>
                $emailable->getMorphClass(),

            'emailable_id' =>
                $emailable->getKey(),

            'recipient_email' =>
                $recipientEmail,

            'recipient_name' =>
                $recipientName,

            'mail_type' =>
                $mailType,

            'subject' =>
                $subject,

            'mailable_class' =>
                $mailable::class,

            'status' =>
                EmailDelivery::ESTADO_PENDIENTE,

            'attempts' =>
                0,

            'queued_at' =>
                now(),

            'metadata' =>
                $metadata,
        ]);
    }

    /**
     * Agregar automáticamente a metadata el ID requerido
     * para reconstruir cada Mailable dentro del Job.
     */
    private function prepararMetadata(
        Model $emailable,
        Mailable $mailable,
        array $metadata
    ): array {
        $automaticMetadata = match (
            $mailable::class
        ) {
            IncidenciaMail::class => [
                'incidencia_id' =>
                    $emailable->getKey(),
            ],

            SolicitudMail::class => [
                'solicitud_id' =>
                    $emailable->getKey(),
            ],

            PaseTemporalMail::class,
            AutorizacionMail::class => [
                'memorando_id' =>
                    $emailable->getKey(),
            ],

            default =>
                throw new RuntimeException(
                    'No existe una configuración de metadata para el Mailable: '
                    .$mailable::class
                ),
        };

        /*
         * La metadata automática tiene prioridad para evitar que
         * accidentalmente se despache un ID incorrecto.
         */
        return array_merge(
            $metadata,
            $automaticMetadata
        );
    }
}