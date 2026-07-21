<?php

namespace App\Services\Mail;

use App\Models\EmailDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TrackedMailService
{
    /**
     * Envía un correo y registra todo el proceso.
     *
     * El registro principal —incidencia, solicitud o memorando—
     * no se elimina si el SMTP falla.
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
        $delivery = EmailDelivery::create([
            'emailable_type' => $emailable->getMorphClass(),
            'emailable_id' => $emailable->getKey(),

            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,

            'mail_type' => $mailType,
            'subject' => $subject,
            'mailable_class' => $mailable::class,

            'status' => EmailDelivery::ESTADO_PENDIENTE,
            'attempts' => 0,

            'queued_at' => now(),
            'metadata' => $metadata,
        ]);

        try {
            $delivery->marcarEnviando();

            $sentMessage = Mail::to(
                $recipientEmail,
                $recipientName
            )->send($mailable);

            $providerMessageId = null;

            if (
                is_object($sentMessage)
                && method_exists($sentMessage, 'getMessageId')
            ) {
                $providerMessageId = $sentMessage->getMessageId();
            }

            $delivery->marcarEnviado(
                $providerMessageId
            );

        } catch (Throwable $exception) {
            $delivery->marcarFallido(
                error: $exception,
                errorCode: (string) $exception->getCode()
            );

            Log::error(
                'Falló el envío de un correo registrado.',
                [
                    'email_delivery_id' => $delivery->id,
                    'emailable_type' => $emailable->getMorphClass(),
                    'emailable_id' => $emailable->getKey(),
                    'mail_type' => $mailType,
                    'recipient_email' => $recipientEmail,
                    'error' => $exception->getMessage(),
                ]
            );
        }

        return $delivery->fresh();
    }
}