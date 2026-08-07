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
    /*
    |--------------------------------------------------------------------------
    | Enviar correo de forma síncrona
    |--------------------------------------------------------------------------
    |
    | Registra el envío, protege la metadata sensible, ejecuta el envío
    | inmediatamente y actualiza el estado del registro según el resultado.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Enviar correo de forma asíncrona
    |--------------------------------------------------------------------------
    |
    | Registra el correo junto con la metadata necesaria y despacha un Job
    | para completar el envío posteriormente mediante la cola configurada.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Crear registro de entrega
    |--------------------------------------------------------------------------
    |
    | Genera el registro EmailDelivery asociado al modelo, destinatario, tipo de correo y metadata antes de ejecutar o encolar el envío.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Preparar metadata del correo
    |--------------------------------------------------------------------------
    |
    | Agrega la información necesaria para reconstruir cada Mailable y
    | protege los datos sensibles de autenticación mediante cifrado con
    | APP_KEY antes de almacenarlos en email_deliveries.metadata.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Proteger metadata sensible
    |--------------------------------------------------------------------------
    |
    | Cifra secretos utilizados por correos de autenticación cuando estos
    | se envían mediante el flujo síncrono y elimina sus versiones en texto
    | plano antes de registrar la entrega.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Eliminar secretos en texto plano
    |--------------------------------------------------------------------------
    |
    | Retira de la metadata cualquier clave sensible que no debe persistirse sin cifrado, como URLs, códigos o tokens de autenticación.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Obtener metadata original
    |--------------------------------------------------------------------------
    |
    | Recupera un valor obligatorio desde la metadata recibida originalmente antes de aplicar filtros o transformaciones.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Validar valor de metadata
    |--------------------------------------------------------------------------
    |
    | Obtiene y valida un valor textual obligatorio de la metadata, generando una excepción cuando el dato no está disponible.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Construir contexto seguro de error
    |--------------------------------------------------------------------------
    |
    | Genera la información utilizada en los registros de error sin incluir secretos ni contenido sensible del correo.
    |
    */

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