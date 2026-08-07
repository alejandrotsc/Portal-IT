<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aviso extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada por el modelo para almacenar y consultar
    | los avisos registrados dentro del Portal TI.
    |
    */

    protected $table = 'avisos';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un aviso.
    |
    */

    protected $fillable = [
        'titulo',
        'mensaje',
        'fecha_inicio',
        'fecha_fin',
        'notificacion_enviada_at',
        'activo',
        'creado_por',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente las fechas a objetos de fecha y el estado
    | activo a un valor booleano al interactuar con el modelo.
    |
    */

    protected function casts(): array
    {
        return [
            'fecha_inicio' =>
                'datetime',

            'fecha_fin' =>
                'datetime',

            'notificacion_enviada_at' =>
                'datetime',

            'activo' =>
                'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function creador(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'creado_por'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Estado del aviso
    |--------------------------------------------------------------------------
    */

    public function estaProgramado(): bool
    {
        return $this->activo
            && $this->fecha_inicio
            && $this->fecha_inicio->isFuture();
    }

    public function estaFinalizado(): bool
    {
        return $this->fecha_fin
            && $this->fecha_fin->isPast();
    }

    public function estaVisible(): bool
    {
        if (! $this->activo) {
            return false;
        }

        if (
            $this->fecha_inicio
            && $this->fecha_inicio->isFuture()
        ) {
            return false;
        }

        if (
            $this->fecha_fin
            && $this->fecha_fin->isPast()
        ) {
            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Estado de notificación
    |--------------------------------------------------------------------------
    */

    public function fueNotificado(): bool
    {
        return $this->notificacion_enviada_at
            !== null;
    }

    public function pendienteDeNotificar(): bool
    {
        return ! $this->fueNotificado();
    }

    public function marcarComoNotificado(): bool
    {
        return $this->forceFill([
            'notificacion_enviada_at' =>
                now(),
        ])->save();
    }
}