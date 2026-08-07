<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incidencia extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Estados
    |--------------------------------------------------------------------------
    |
    | Define los estados disponibles para representar el ciclo de vida
    | de una incidencia dentro del Portal TI.
    |
    */

    public const ESTADO_ABIERTA =
        'Abierta';

    public const ESTADO_EN_PROCESO =
        'En_proceso';

    public const ESTADO_RESUELTA =
        'Resuelta';

    public const ESTADOS = [
        self::ESTADO_ABIERTA,
        self::ESTADO_EN_PROCESO,
        self::ESTADO_RESUELTA,
    ];

    /*
    |--------------------------------------------------------------------------
    | Prioridades
    |--------------------------------------------------------------------------
    |
    | Define los niveles de prioridad disponibles para clasificar las
    | incidencias según su impacto o urgencia.
    |
    */

    public const PRIORIDAD_BAJA =
        'Baja';

    public const PRIORIDAD_MEDIA =
        'Media';

    public const PRIORIDAD_ALTA =
        'Alta';

    public const PRIORIDAD_CRITICA =
        'Critica';

    public const PRIORIDADES = [
        self::PRIORIDAD_BAJA,
        self::PRIORIDAD_MEDIA,
        self::PRIORIDAD_ALTA,
        self::PRIORIDAD_CRITICA,
    ];

    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar las incidencias registradas
    | por los usuarios dentro del Portal TI.
    |
    */

    protected $table =
        'incidencias';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de una incidencia.
    |
    */

    protected $fillable = [
        'codigo',
        'usuario_id',
        'titulo',
        'descripcion',
        'tiempo_problema',
        'afectacion',
        'equipo',
        'ubicacion',
        'estado',
        'prioridad',
        'correo_enviado',
        'fecha_envio_correo',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente el estado de envío del correo a booleano
    | y la fecha de envío a un objeto de fecha y hora.
    |
    */

    protected $casts = [
        'correo_enviado' =>
            'boolean',

        'fecha_envio_correo' =>
            'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Consultar estado
    |--------------------------------------------------------------------------
    |
    | Proporciona métodos auxiliares para determinar rápidamente el
    | estado actual en el que se encuentra una incidencia.
    |
    */

    public function estaAbierta(): bool
    {
        return $this->estado
            === self::ESTADO_ABIERTA;
    }

    public function estaEnProceso(): bool
    {
        return $this->estado
            === self::ESTADO_EN_PROCESO;
    }

    public function estaResuelta(): bool
    {
        return $this->estado
            === self::ESTADO_RESUELTA;
    }

    /*
    |--------------------------------------------------------------------------
    | Usuario creador
    |--------------------------------------------------------------------------
    |
    | Define la relación con el usuario que registró originalmente
    | la incidencia dentro del sistema.
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
    | Archivos adjuntos
    |--------------------------------------------------------------------------
    |
    | Define la relación con las evidencias o archivos que fueron
    | adjuntados a la incidencia durante su registro.
    |
    */

    public function archivos(): HasMany
    {
        return $this->hasMany(
            IncidenciaArchivo::class,
            'incidencia_id'
        );
    }
}