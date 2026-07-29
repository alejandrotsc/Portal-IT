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
    | Campos ocultos al serializar
    |--------------------------------------------------------------------------
    |
    | Evita que información interna o sensible aparezca accidentalmente
    | en respuestas JSON generadas directamente desde el modelo.
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
    | Conversión de valores
    |--------------------------------------------------------------------------
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
    */

    public function marcarEnviando(): void
    {
        /*
         * Incrementa el intento y actualiza el estado en una sola operación SQL.
         * Esto reduce inconsistencias si dos procesos intentan actualizar el mismo
         * registro casi al mismo tiempo.
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
    */

    private function normalizarErrorSeguro(
        Throwable|string $error
    ): string {
        if ($error instanceof Throwable) {
            /*
             * Se conserva la clase para diagnóstico, pero no el mensaje completo
             * de la excepción, ya que podría contener rutas, tokens, correos o
             * detalles del servidor SMTP.
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
         * Elimina patrones sensibles comunes antes de almacenar el texto.
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