<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardiaSoporte extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar las asignaciones de
    | guardias de soporte correspondientes a los agentes de TI.
    |
    */

    protected $table = 'guardias_soporte';

    /*
    |--------------------------------------------------------------------------
    | Ubicaciones permitidas
    |--------------------------------------------------------------------------
    |
    | Define las ubicaciones disponibles para asignar una guardia de
    | soporte dentro de la organización.
    |
    */

    public const UBICACION_TVC = 'TVC';
    public const UBICACION_CNT = 'CNT';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de una guardia de soporte.
    |
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
    |
    | Convierte automáticamente la fecha de la guardia a un objeto de
    | fecha y el estado activo a un valor booleano.
    |
    */

    protected $casts = [
        'fecha' => 'date',
        'activo' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Agente asignado
    |--------------------------------------------------------------------------
    |
    | Define la relación con el usuario que ha sido asignado como agente
    | responsable de cubrir la guardia de soporte.
    |
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
    |
    | Define la relación con el usuario que registró originalmente la
    | asignación de la guardia de soporte.
    |
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
    |
    | Filtra las guardias para obtener únicamente aquellas que se
    | encuentran habilitadas actualmente.
    |
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
    |
    | Obtiene las guardias activas cuya fecha corresponde al día actual
    | o a una fecha futura y las ordena cronológicamente.
    |
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
    |
    | Filtra las asignaciones de guardia correspondientes a un agente
    | específico mediante su identificador de usuario.
    |
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
    |
    | Devuelve el nombre correspondiente a la ubicación asignada a la
    | guardia utilizando las ubicaciones permitidas por el modelo.
    |
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
    |
    | Obtiene el nombre del día correspondiente a la fecha de la guardia
    | utilizando la configuración regional en español.
    |
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
    |
    | Construye una representación legible del horario de la guardia
    | utilizando únicamente las horas y minutos de inicio y finalización.
    |
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