<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Throwable;

class EmailDelivery extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla
    |--------------------------------------------------------------------------
    */

    protected $table = 'email_deliveries';


    /*
    |--------------------------------------------------------------------------
    | Estados
    |--------------------------------------------------------------------------
    */

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_ENVIANDO = 'enviando';

    public const ESTADO_ENVIADO = 'enviado';

    public const ESTADO_FALLIDO = 'fallido';


    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
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
    | Conversión de valores
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'emailable_id' =>
            'integer',

        'attempts' =>
            'integer',

        'metadata' =>
            'array',

        'queued_at' =>
            'datetime',

        'last_attempt_at' =>
            'datetime',

        'sent_at' =>
            'datetime',

        'failed_at' =>
            'datetime',

        'next_retry_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Valores predeterminados
    |--------------------------------------------------------------------------
    */

    protected $attributes = [

        'status' =>
            self::ESTADO_PENDIENTE,

        'attempts' =>
            0,

        'metadata' =>
            '{}',

    ];


    /*
    |--------------------------------------------------------------------------
    | Gestión relacionada
    |--------------------------------------------------------------------------
    |
    | Puede devolver:
    |
    | - Incidencia
    | - Solicitud
    | - Memorando
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
    */

    public function scopePendientes(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::ESTADO_PENDIENTE
        );
    }


    public function scopeEnviando(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::ESTADO_ENVIANDO
        );
    }


    public function scopeEnviados(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::ESTADO_ENVIADO
        );
    }


    public function scopeFallidos(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::ESTADO_FALLIDO
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Consultar estado actual
    |--------------------------------------------------------------------------
    */

    public function estaPendiente(): bool
    {
        return $this->status
            ===
            self::ESTADO_PENDIENTE;
    }


    public function estaEnviando(): bool
    {
        return $this->status
            ===
            self::ESTADO_ENVIANDO;
    }


    public function fueEnviado(): bool
    {
        return $this->status
            ===
            self::ESTADO_ENVIADO;
    }


    public function fallo(): bool
    {
        return $this->status
            ===
            self::ESTADO_FALLIDO;
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar como enviando
    |--------------------------------------------------------------------------
    */

    public function marcarEnviando(): void
    {
        $this->increment(
            'attempts'
        );


        $this->update([

            'status' =>
                self::ESTADO_ENVIANDO,

            'last_attempt_at' =>
                now(),

            'last_error' =>
                null,

            'error_code' =>
                null,

            'failed_at' =>
                null,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar como enviado
    |--------------------------------------------------------------------------
    */

    public function marcarEnviado(
        ?string $providerMessageId = null
    ): void {
        $this->update([

            'status' =>
                self::ESTADO_ENVIADO,

            'sent_at' =>
                now(),

            'failed_at' =>
                null,

            'next_retry_at' =>
                null,

            'last_error' =>
                null,

            'error_code' =>
                null,

            'provider_message_id' =>
                $providerMessageId,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar como fallido
    |--------------------------------------------------------------------------
    */

    public function marcarFallido(
        Throwable|string $error,
        ?string $errorCode = null,
        mixed $nextRetryAt = null
    ): void {
        $message = $error instanceof Throwable
            ? $error->getMessage()
            : $error;


        /*
         * Evitar almacenar mensajes excesivamente grandes.
         */
        $message = mb_substr(
            trim($message),
            0,
            4000
        );


        $this->update([

            'status' =>
                self::ESTADO_FALLIDO,

            'last_error' =>
                $message,

            'error_code' =>
                $errorCode,

            'failed_at' =>
                now(),

            'next_retry_at' =>
                $nextRetryAt,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Volver a poner en cola
    |--------------------------------------------------------------------------
    */

    public function marcarPendiente(
        mixed $nextRetryAt = null
    ): void {
        $this->update([

            'status' =>
                self::ESTADO_PENDIENTE,

            'next_retry_at' =>
                $nextRetryAt,

            'failed_at' =>
                null,

        ]);
    }
}