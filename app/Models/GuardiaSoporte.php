<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardiaSoporte extends Model
{
    protected $table = 'guardias_soporte';

    /*
    |--------------------------------------------------------------------------
    | Ubicaciones permitidas
    |--------------------------------------------------------------------------
    */

    public const UBICACION_TVC = 'TVC';

    public const UBICACION_CNT = 'CNT';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'usuario_id',
        'creado_por',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'ubicacion',
        'observacion',
        'activo',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'fecha' => 'date',
        'activo' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Agente asignado
    |--------------------------------------------------------------------------
    */

    public function agente(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Administrador que creó la asignación
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
    | Guardias activas
    |--------------------------------------------------------------------------
    */

    public function scopeActivas(
        Builder $query
    ): Builder {
        return $query->where(
            'activo',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardias próximas
    |--------------------------------------------------------------------------
    */

    public function scopeProximas(
        Builder $query
    ): Builder {
        return $query
            ->activas()
            ->whereDate(
                'fecha',
                '>=',
                today()
            )
            ->orderBy('fecha')
            ->orderBy('hora_inicio');
    }

    /*
    |--------------------------------------------------------------------------
    | Guardias de un agente
    |--------------------------------------------------------------------------
    */

    public function scopeDelAgente(
        Builder $query,
        int $usuarioId
    ): Builder {
        return $query->where(
            'usuario_id',
            $usuarioId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Nombre de la ubicación
    |--------------------------------------------------------------------------
    */

    public function getNombreUbicacionAttribute(): string
    {
        return match ($this->ubicacion) {
            self::UBICACION_TVC =>
                'TVC',

            self::UBICACION_CNT =>
                'CNT',

            default =>
                $this->ubicacion,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Día de la guardia
    |--------------------------------------------------------------------------
    */

    public function getDiaAttribute(): string
    {
        return $this->fecha
            ->locale('es')
            ->translatedFormat('l');
    }

    /*
    |--------------------------------------------------------------------------
    | Horario formateado
    |--------------------------------------------------------------------------
    */

    public function getHorarioAttribute(): string
    {
        return substr(
            $this->hora_inicio,
            0,
            5
        )
            . ' – '
            . substr(
                $this->hora_fin,
                0,
                5
            );
    }
}