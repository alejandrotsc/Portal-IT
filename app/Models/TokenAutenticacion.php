<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenAutenticacion extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los tokens temporales
    | empleados durante los procesos de autenticación del Portal TI.
    |
    */

    protected $table = 'tokens_autenticacion';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un token de autenticación.
    |
    */

    protected $fillable = [
        'usuario_id',
        'correo',
        'token_hash',
        'tipo',
        'expires_at',
        'used_at',
        'attempts',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente las fechas de expiración y utilización
    | a objetos de fecha y la cantidad de intentos a un valor entero.
    |
    */

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Tipos de token
    |--------------------------------------------------------------------------
    |
    | Define los tipos de tokens disponibles según el proceso de
    | autenticación para el que fueron generados.
    |
    */

    public const TIPO_REGISTRO = 'registro';

    public const TIPO_LOGIN = 'login';

    /*
    |--------------------------------------------------------------------------
    | Usuario relacionado
    |--------------------------------------------------------------------------
    |
    | Define la relación con el usuario al que pertenece el token de
    | autenticación generado.
    |
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tokens disponibles
    |--------------------------------------------------------------------------
    |
    | Filtra los tokens que todavía no han sido utilizados y cuya fecha
    | de expiración aún no ha sido alcanzada.
    |
    */

    public function scopeDisponible(Builder $query): Builder
    {
        return $query
            ->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar expiración
    |--------------------------------------------------------------------------
    |
    | Determina si el token ha superado su fecha de expiración y ya no
    | puede ser utilizado para completar la autenticación.
    |
    */

    public function estaVencido(): bool
    {
        return $this->expires_at->isPast();
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar utilización
    |--------------------------------------------------------------------------
    |
    | Determina si el token ya fue utilizado previamente durante un
    | proceso de autenticación.
    |
    */

    public function fueUtilizado(): bool
    {
        return $this->used_at !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Marcar como utilizado
    |--------------------------------------------------------------------------
    |
    | Registra la fecha y hora en la que el token fue utilizado para
    | impedir que pueda volver a emplearse posteriormente.
    |
    */

    public function marcarComoUtilizado(): void
    {
        $this->update([
            'used_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Incrementar intentos
    |--------------------------------------------------------------------------
    |
    | Incrementa la cantidad de intentos realizados con el token para
    | mantener un registro de los intentos de validación efectuados.
    |
    */

    public function incrementarIntentos(): void
    {
        $this->increment('attempts');
    }
}