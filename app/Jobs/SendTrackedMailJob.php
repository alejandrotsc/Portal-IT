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
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

/*
|--------------------------------------------------------------------------
| Job de envío de correo con seguimiento
|--------------------------------------------------------------------------
|
| Procesa los correos registrados en EmailDelivery, aplica reintentos,
| reconstruye el Mailable correspondiente, actualiza el estado del envío y
| sincroniza el modelo relacionado cuando SMTP confirma la entrega.
|
*/

class SendTrackedMailJob implements ShouldQueue
{
    use Queueable;

    /*
    |--------------------------------------------------------------------------
    | Cantidad máxima de intentos
    |--------------------------------------------------------------------------
    |
    | Define cuántas veces puede ejecutarse el Job antes de considerarse
    | definitivamente fallido.
    |
    */
    public int $tries = 3;

    /*
    |--------------------------------------------------------------------------
    | Tiempo máximo por intento
    |--------------------------------------------------------------------------
    |
    | Limita la duración de cada ejecución individual del Job.
    |
    */
    public int $timeout = 120;

    /*
    |--------------------------------------------------------------------------
    | Fallar cuando se supera el timeout
    |--------------------------------------------------------------------------
    |
    | Indica al worker que marque el Job como fallido cuando exceda el tiempo
    | máximo configurado.
    |
    */
    public bool $failOnTimeout = true;

    /*
    |--------------------------------------------------------------------------
    | Identificador de seguimiento
    |--------------------------------------------------------------------------
    |
    | Recibe el ID de EmailDelivery y asigna el trabajo a la cola exclusiva
    | utilizada para procesar correos.
    |
    */
    public function __construct(
        public readonly int $emailDeliveryId
    ) {
        $this->onQueue('emails');
    }

    /*
    |--------------------------------------------------------------------------
    | Esperas entre reintentos
    |--------------------------------------------------------------------------
    |
    | Define los intervalos progresivos aplicados después de cada fallo.
    |
    */
    public function backoff(): array
    {
        return [
            30,
            120,
            300,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Procesar envío del correo
    |--------------------------------------------------------------------------
    |
    | Recupera la entrega, evita reenvíos, reconstruye el Mailable, intenta el
    | envío y actualiza estados, reintentos y logs según el resultado.
    |
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
        |
        | Si la entrega ya fue confirmada como enviada, el Job termina sin
        | intentar nuevamente el correo.
        |
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
            /*
            |--------------------------------------------------------------------------
            | Marcar intento en curso
            |--------------------------------------------------------------------------
            */

            $delivery->marcarEnviando();

            /*
            |--------------------------------------------------------------------------
            | Reconstruir correo
            |--------------------------------------------------------------------------
            */

            $mailable = $this->reconstruirMailable(
                $delivery
            );

            /*
            |--------------------------------------------------------------------------
            | Enviar mediante SMTP
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Confirmar entrega
            |--------------------------------------------------------------------------
            */

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

                    'provider_message_id' =>
                        $providerMessageId,
                ]
            );

        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Gestionar fallo y reintento
            |--------------------------------------------------------------------------
            |
            | Determina si quedan intentos, programa la siguiente ejecución o
            | marca definitivamente la entrega como fallida.
            |
            */

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
                        $this->descripcionSeguraError($exception),

                    'last_attempt_at' =>
                        now(),
                ])->save();

            } else {
                $delivery->marcarFallido(
                    error:
                        $this->descripcionSeguraError($exception),

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

                    'attempt' =>
                        $intentoActual,

                    'tries' =>
                        $this->tries,

                    'will_retry' =>
                        $quedanIntentos,

                    'next_retry_at' =>
                        $nextRetryAt?->toDateTimeString(),

                    'exception_class' =>
                        $exception::class,

                    'error_code' =>
                        (string) $exception->getCode(),
                ]
            );

            throw $exception;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reconstruir Mailable
    |--------------------------------------------------------------------------
    |
    | Selecciona el constructor correspondiente según la clase almacenada en
    | EmailDelivery y reconstruye el correo a partir de su metadata.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Reconstruir correo de incidencia
    |--------------------------------------------------------------------------
    |
    | Recupera la incidencia asociada y genera su Mailable con las relaciones
    | necesarias para construir el mensaje.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Reconstruir correo de solicitud
    |--------------------------------------------------------------------------
    |
    | Recupera la solicitud relacionada y construye el Mailable correspondiente.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Reconstruir correo de pase temporal
    |--------------------------------------------------------------------------
    |
    | Recupera el memorando asociado al pase menor a 24 horas y reconstruye su
    | Mailable.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Reconstruir correo de autorización
    |--------------------------------------------------------------------------
    |
    | Recupera el memorando, tipo y solicitante requeridos para reconstruir el
    | correo de autorización.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Reconstruir correo de enlace mágico
    |--------------------------------------------------------------------------
    |
    | Recupera el usuario y descifra la URL almacenada en metadata antes de
    | construir el Mailable de acceso.
    |
    */
    private function crearEnlaceMagicoMail(
        array $metadata
    ): EnlaceMagicoMail {
        $usuarioId =
            $this->obtenerIdMetadata(
                $metadata,
                'usuario_id'
            );

        $url = $this->obtenerSecretoMetadata(
            metadata: $metadata,
            encryptedKey: 'url_cifrada',
            legacyKey: 'url',
            descripcion: 'URL del enlace mágico'
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

    /*
    |--------------------------------------------------------------------------
    | Reconstruir correo de verificación
    |--------------------------------------------------------------------------
    |
    | Recupera el usuario y descifra el código almacenado para reconstruir el
    | Mailable de verificación.
    |
    */
    private function crearCodigoVerificacionMail(
        array $metadata
    ): CodigoVerificacionMail {
        $usuarioId =
            $this->obtenerIdMetadata(
                $metadata,
                'usuario_id'
            );

        $codigo = $this->obtenerSecretoMetadata(
            metadata: $metadata,
            encryptedKey: 'codigo_cifrado',
            legacyKey: 'codigo',
            descripcion: 'código de verificación'
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

    /*
    |--------------------------------------------------------------------------
    | Obtener secreto desde metadata
    |--------------------------------------------------------------------------
    |
    | Descifra valores sensibles almacenados en metadata y mantiene
    | compatibilidad con claves antiguas en texto plano para trabajos previos.
    |
    | Los registros nuevos deben utilizar exclusivamente las claves cifradas.
    |
    */
    private function obtenerSecretoMetadata(
        array $metadata,
        string $encryptedKey,
        string $legacyKey,
        string $descripcion
    ): string {
        $encryptedValue = $metadata[$encryptedKey] ?? null;

        if (is_string($encryptedValue) && trim($encryptedValue) !== '') {
            try {
                $value = Crypt::decryptString($encryptedValue);
            } catch (DecryptException $exception) {
                throw new RuntimeException(
                    "No fue posible descifrar {$descripcion}.",
                    previous: $exception
                );
            }

            if (trim($value) === '') {
                throw new RuntimeException(
                    "La metadata del correo no contiene {$descripcion} válida."
                );
            }

            return trim($value);
        }

        $legacyValue = $metadata[$legacyKey] ?? null;

        if (is_string($legacyValue) && trim($legacyValue) !== '') {
            Log::warning(
                'Se procesó metadata antigua de autenticación sin cifrar.',
                [
                    'email_delivery_id' => $this->emailDeliveryId,
                    'legacy_key' => $legacyKey,
                ]
            );

            return trim($legacyValue);
        }

        throw new RuntimeException(
            "La metadata del correo no contiene {$descripcion} válida."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener texto desde metadata
    |--------------------------------------------------------------------------
    |
    | Recupera y valida un valor textual obligatorio almacenado en la metadata
    | del correo.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Obtener ID desde metadata
    |--------------------------------------------------------------------------
    |
    | Recupera un identificador numérico positivo y genera una excepción cuando
    | la metadata no contiene un valor válido.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Actualizar modelo relacionado
    |--------------------------------------------------------------------------
    |
    | Marca el modelo padre como enviado cuando dispone de las columnas
    | correspondientes, manteniendo compatibilidad entre distintos modelos.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Generar descripción segura del error
    |--------------------------------------------------------------------------
    |
    | Reduce la información de la excepción a una descripción adecuada para
    | almacenamiento y logs sin exponer detalles sensibles.
    |
    */
    private function descripcionSeguraError(Throwable $exception): string
    {
        $codigo = (string) $exception->getCode();

        return sprintf(
            'Falló el procesamiento del correo (%s%s).',
            $exception::class,
            $codigo !== '' && $codigo !== '0'
                ? ", código {$codigo}"
                : ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calcular siguiente reintento
    |--------------------------------------------------------------------------
    |
    | Determina la fecha del próximo intento utilizando el backoff configurado
    | y el número actual de ejecuciones.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Registrar fallo definitivo
    |--------------------------------------------------------------------------
    |
    | Marca la entrega como fallida cuando ya no habrá más reintentos y deja un
    | registro crítico para diagnóstico.
    |
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

                    'exception_class' =>
                        $exception ? $exception::class : null,

                    'error_code' =>
                        $exception ? (string) $exception->getCode() : null,
                ]
            );

            return;
        }

        if (! $delivery->fueEnviado()) {
            $delivery->marcarFallido(
                error:
                    $exception
                        ? $this->descripcionSeguraError($exception)
                        : 'El trabajo de correo falló definitivamente.',

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

                'attempts' =>
                    $delivery->fresh()?->attempts,

                    'exception_class' =>
                        $exception ? $exception::class : null,

                    'error_code' =>
                        $exception ? (string) $exception->getCode() : null,
            ]
        );
    }
}