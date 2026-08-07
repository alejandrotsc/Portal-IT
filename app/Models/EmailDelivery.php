<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Throwable;

class EmailDelivery extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar el seguimiento de los
    | correos generados y enviados por las distintas gestiones.
    |
    */

    protected $table = 'email_deliveries';

    /*
    |--------------------------------------------------------------------------
    | Estados disponibles
    |--------------------------------------------------------------------------
    |
    | Define los diferentes estados que puede tener un correo durante
    | su procesamiento, envío y gestión de posibles errores.
    |
    */

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_ENVIANDO = 'enviando';
    public const ESTADO_ENVIADO = 'enviado';
    public const ESTADO_FALLIDO = 'fallido';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de una entrega de correo.
    |
    */

    protected $fillable = [
        'emailable_type',
        'emailable_id',
        'recipient_email',
        'recipient_name',
        'mail_type',
        'subject',
        'mailable_class',
        'status',
        'attempts',
        'last_error',
        'error_code',
        'provider_message_id',
        'queued_at',
        'last_attempt_at',
        'sent_at',
        'failed_at',
        'next_retry_at',
        'metadata',
    ];

    /*
    |--------------------------------------------------------------------------
    | Campos ocultos al serializar
    |--------------------------------------------------------------------------
    |
    | Evita que información interna o sensible aparezca accidentalmente
    | en respuestas JSON generadas directamente desde el modelo.
    |
    */

    protected $hidden = [
        'metadata',
        'last_error',
        'error_code',
        'provider_message_id',
        'mailable_class',
        'recipient_email',
        'recipient_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente identificadores, intentos, metadatos y
    | fechas a los tipos correspondientes al interactuar con el modelo.
    |
    */

    protected $casts = [
        'emailable_id' => 'integer',
        'attempts' => 'integer',
        'metadata' => 'array',
        'queued_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Valores predeterminados
    |--------------------------------------------------------------------------
    |
    | Establece el estado inicial, la cantidad inicial de intentos y
    | los metadatos utilizados al crear una nueva entrega de correo.
    |
    */

    protected $attributes = [
        'status' => self::ESTADO_PENDIENTE,
        'attempts' => 0,
        'metadata' => '{}',
    ];

    /*
    |--------------------------------------------------------------------------
    | Gestión relacionada
    |--------------------------------------------------------------------------
    |
    | Define la relación polimórfica con la incidencia, solicitud,
    | memorando u otra gestión que originó el envío del correo.
    |
    */

    public function emailable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Consultas por estado
    |--------------------------------------------------------------------------
    |
    | Permite filtrar las entregas de correo según el estado actual
    | en el que se encuentra cada proceso de envío.
    |
    */

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('status', self::ESTADO_PENDIENTE);
    }

    public function scopeEnviando(Builder $query): Builder
    {
        return $query->where('status', self::ESTADO_ENVIANDO);
    }

    public function scopeEnviados(Builder $query): Builder
    {
        return $query->where('status', self::ESTADO_ENVIADO);
    }

    public function scopeFallidos(Builder $query): Builder
    {
        return $query->where('status', self::ESTADO_FALLIDO);
    }

    /*
    |--------------------------------------------------------------------------
    | Consultar estado actual
    |--------------------------------------------------------------------------
    |
    | Proporciona métodos auxiliares para determinar rápidamente el
    | estado actual de una entrega de correo.
    |
    */

    public function estaPendiente(): bool
    {
        return $this->status === self::ESTADO_PENDIENTE;
    }

    public function estaEnviando(): bool
    {
        return $this->status === self::ESTADO_ENVIANDO;
    }

    public function fueEnviado(): bool
    {
        return $this->status === self::ESTADO_ENVIADO;
    }

    public function fallo(): bool
    {
        return $this->status === self::ESTADO_FALLIDO;
    }

    /*
    |--------------------------------------------------------------------------
    | Marcar como enviando
    |--------------------------------------------------------------------------
    |
    | Incrementa la cantidad de intentos y actualiza el registro para
    | indicar que el correo se encuentra actualmente en procesamiento.
    |
    */

    public function marcarEnviando(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Actualizar intento de envío
        |--------------------------------------------------------------------------
        |
        | Incrementa el intento y actualiza el estado en una sola operación
        | SQL para reducir inconsistencias si varios procesos intentan
        | modificar el mismo registro casi simultáneamente.
        |
        */

        $this->increment(
            'attempts',
            1,
            [
                'status' => self::ESTADO_ENVIANDO,
                'last_attempt_at' => now(),
                'last_error' => null,
                'error_code' => null,
                'failed_at' => null,
                'next_retry_at' => null,
            ]
        );

        $this->refresh();
    }

    /*
    |--------------------------------------------------------------------------
    | Marcar como enviado
    |--------------------------------------------------------------------------
    |
    | Registra que el correo fue enviado correctamente, almacena la fecha
    | correspondiente y elimina cualquier información de error previa.
    |
    */

    public function marcarEnviado(
        ?string $providerMessageId = null
    ): void {
        $this->update([
            'status' => self::ESTADO_ENVIADO,
            'sent_at' => now(),
            'failed_at' => null,
            'next_retry_at' => null,
            'last_error' => null,
            'error_code' => null,
            'provider_message_id' => $providerMessageId
                ? Str::limit(trim($providerMessageId), 500, '')
                : null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Marcar como fallido
    |--------------------------------------------------------------------------
    |
    | Registra un fallo durante el envío, almacena información segura del
    | error y permite definir cuándo deberá realizarse el siguiente intento.
    |
    */

    public function marcarFallido(
        Throwable|string $error,
        ?string $errorCode = null,
        mixed $nextRetryAt = null
    ): void {
        $mensajeSeguro = $this->normalizarErrorSeguro($error);

        $this->update([
            'status' => self::ESTADO_FALLIDO,
            'last_error' => $mensajeSeguro,
            'error_code' => $errorCode
                ? Str::limit(trim($errorCode), 100, '')
                : null,
            'failed_at' => now(),
            'next_retry_at' => $nextRetryAt,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Volver a poner en cola
    |--------------------------------------------------------------------------
    |
    | Restablece una entrega al estado pendiente para permitir un nuevo
    | intento de envío y opcionalmente establece la fecha del reintento.
    |
    */

    public function marcarPendiente(
        mixed $nextRetryAt = null
    ): void {
        $this->update([
            'status' => self::ESTADO_PENDIENTE,
            'next_retry_at' => $nextRetryAt,
            'failed_at' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitizar errores antes de almacenarlos
    |--------------------------------------------------------------------------
    |
    | Genera una versión segura del error eliminando información que
    | podría revelar datos sensibles antes de guardarla en la base.
    |
    */

    private function normalizarErrorSeguro(
        Throwable|string $error
    ): string {
        if ($error instanceof Throwable) {
            /*
            |--------------------------------------------------------------------------
            | Normalizar excepciones
            |--------------------------------------------------------------------------
            |
            | Se conserva únicamente la clase de la excepción para facilitar
            | el diagnóstico sin almacenar rutas, tokens, correos u otros
            | detalles sensibles que podrían aparecer en el mensaje original.
            |
            */

            return Str::limit(
                'Error de tipo '.$error::class,
                500,
                ''
            );
        }

        $mensaje = trim($error);

        if ($mensaje === '') {
            return 'Error de envío no especificado.';
        }

        /*
        |--------------------------------------------------------------------------
        | Eliminar información sensible
        |--------------------------------------------------------------------------
        |
        | Sustituye patrones comunes como URLs, cadenas extensas, códigos
        | numéricos y direcciones de correo antes de almacenar el error.
        |
        */

        $mensaje = preg_replace(
            [
                '/https?:\/\/\S+/iu',
                '/\b[A-Fa-f0-9]{40,}\b/u',
                '/\b\d{6}\b/u',
                '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu',
            ],
            '[dato protegido]',
            $mensaje
        ) ?? 'Error de envío no especificado.';

        return Str::limit($mensaje, 1000, '');
    }
}