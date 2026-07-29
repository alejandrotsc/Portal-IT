<?php

namespace App\Services\Mail;

use App\Jobs\SendTrackedMailJob;
use App\Mail\AutorizacionMail;
use App\Mail\CodigoVerificacionMail;
use App\Mail\EnlaceMagicoMail;
use App\Mail\IncidenciaMail;
use App\Mail\PaseTemporalMail;
use App\Mail\SolicitudMail;
use App\Models\EmailDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class TrackedMailService
{
    /**
     * Envía un correo de forma síncrona y registra todo el proceso.
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
        $metadata = $this->protegerMetadataSensible(
            mailable: $mailable,
            metadata: $metadata
        );

        $delivery = $this->crearDelivery(
            emailable: $emailable,
            mailable: $mailable,
            recipientEmail: $recipientEmail,
            mailType: $mailType,
            recipientName: $recipientName,
            subject: $subject,
            metadata: $metadata
        );

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

            $delivery->marcarEnviado($providerMessageId);
        } catch (Throwable $exception) {
            $delivery->marcarFallido(
                error: $exception,
                errorCode: (string) $exception->getCode()
            );

            Log::error(
                'Falló el envío síncrono de un correo registrado.',
                $this->contextoSeguroDeError(
                    delivery: $delivery,
                    emailable: $emailable,
                    mailType: $mailType,
                    exception: $exception
                )
            );
        }

        return $delivery->fresh();
    }

    /**
     * Registra el correo y lo despacha para enviarse en segundo plano.
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
            emailable: $emailable,
            mailable: $mailable,
            metadata: $metadata
        );

        $delivery = $this->crearDelivery(
            emailable: $emailable,
            mailable: $mailable,
            recipientEmail: $recipientEmail,
            mailType: $mailType,
            recipientName: $recipientName,
            subject: $subject,
            metadata: $metadata
        );

        try {
            SendTrackedMailJob::dispatch($delivery->id)
                ->afterCommit();

            Log::info(
                'Correo registrado y enviado a la cola.',
                [
                    'email_delivery_id' => $delivery->id,
                    'emailable_type' => $emailable->getMorphClass(),
                    'emailable_id' => $emailable->getKey(),
                    'mail_type' => $mailType,
                    'queue' => 'emails',
                ]
            );
        } catch (Throwable $exception) {
            $delivery->marcarFallido(
                error: $exception,
                errorCode: (string) $exception->getCode()
            );

            Log::error(
                'No se pudo despachar el correo registrado a la cola.',
                $this->contextoSeguroDeError(
                    delivery: $delivery,
                    emailable: $emailable,
                    mailType: $mailType,
                    exception: $exception
                )
            );
        }

        return $delivery->fresh();
    }

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
            'emailable_type' => $emailable->getMorphClass(),
            'emailable_id' => $emailable->getKey(),
            'recipient_email' => mb_strtolower(trim($recipientEmail)),
            'recipient_name' => $recipientName
                ? trim($recipientName)
                : null,
            'mail_type' => trim($mailType),
            'subject' => $subject
                ? trim($subject)
                : null,
            'mailable_class' => $mailable::class,
            'status' => EmailDelivery::ESTADO_PENDIENTE,
            'attempts' => 0,
            'queued_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Agrega la metadata necesaria para reconstruir cada Mailable.
     *
     * Los secretos de autenticación se almacenan cifrados con APP_KEY.
     * Nunca deben quedar como texto plano en email_deliveries.metadata.
     */
    private function prepararMetadata(
        Model $emailable,
        Mailable $mailable,
        array $metadata
    ): array {
        $originalMetadata = $metadata;
        $metadata = $this->eliminarClavesSensiblesPlanas($metadata);

        $automaticMetadata = match ($mailable::class) {
            IncidenciaMail::class => [
                'incidencia_id' => $emailable->getKey(),
            ],

            SolicitudMail::class => [
                'solicitud_id' => $emailable->getKey(),
            ],

            PaseTemporalMail::class,
            AutorizacionMail::class => [
                'memorando_id' => $emailable->getKey(),
            ],

            EnlaceMagicoMail::class => [
                'usuario_id' => $emailable->getKey(),
                'url_cifrada' => Crypt::encryptString(
                    $this->obtenerTextoMetadataOriginal(
                        originalMetadata: $originalMetadata,
                        key: 'url',
                        descripcion: 'URL del enlace mágico'
                    )
                ),
                'metadata_version' => 2,
            ],

            CodigoVerificacionMail::class => [
                'usuario_id' => $emailable->getKey(),
                'codigo_cifrado' => Crypt::encryptString(
                    $this->obtenerTextoMetadataOriginal(
                        originalMetadata: $originalMetadata,
                        key: 'codigo',
                        descripcion: 'código de verificación'
                    )
                ),
                'metadata_version' => 2,
            ],

            default => throw new RuntimeException(
                'No existe una configuración de metadata para el Mailable: '
                .$mailable::class
            ),
        };

        return array_merge($metadata, $automaticMetadata);
    }

    /**
     * Protege secretos si en algún momento un correo de autenticación
     * se envía por el método síncrono.
     */
    private function protegerMetadataSensible(
        Mailable $mailable,
        array $metadata
    ): array {
        if ($mailable instanceof EnlaceMagicoMail) {
            $url = $this->obtenerTextoMetadata(
                metadata: $metadata,
                key: 'url',
                descripcion: 'URL del enlace mágico'
            );

            $metadata = $this->eliminarClavesSensiblesPlanas($metadata);
            $metadata['url_cifrada'] = Crypt::encryptString($url);
            $metadata['metadata_version'] = 2;
        }

        if ($mailable instanceof CodigoVerificacionMail) {
            $codigo = $this->obtenerTextoMetadata(
                metadata: $metadata,
                key: 'codigo',
                descripcion: 'código de verificación'
            );

            $metadata = $this->eliminarClavesSensiblesPlanas($metadata);
            $metadata['codigo_cifrado'] = Crypt::encryptString($codigo);
            $metadata['metadata_version'] = 2;
        }

        return $metadata;
    }

    private function eliminarClavesSensiblesPlanas(array $metadata): array
    {
        unset(
            $metadata['url'],
            $metadata['codigo'],
            $metadata['token'],
            $metadata['magic_token']
        );

        return $metadata;
    }

    private function obtenerTextoMetadataOriginal(
        array $originalMetadata,
        string $key,
        string $descripcion
    ): string {
        return $this->obtenerTextoMetadata(
            metadata: $originalMetadata,
            key: $key,
            descripcion: $descripcion
        );
    }

    private function obtenerTextoMetadata(
        array $metadata,
        string $key,
        string $descripcion
    ): string {
        $value = $metadata[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException(
                "No se recibió {$descripcion} en la metadata del correo."
            );
        }

        return trim($value);
    }

    private function contextoSeguroDeError(
        EmailDelivery $delivery,
        Model $emailable,
        string $mailType,
        Throwable $exception
    ): array {
        return [
            'email_delivery_id' => $delivery->id,
            'emailable_type' => $emailable->getMorphClass(),
            'emailable_id' => $emailable->getKey(),
            'mail_type' => $mailType,
            'exception_class' => $exception::class,
            'error_code' => (string) $exception->getCode(),
        ];
    }
}