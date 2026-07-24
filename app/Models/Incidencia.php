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


    protected $table =
        'incidencias';


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
    */

    public function archivos(): HasMany
    {
        return $this->hasMany(
            IncidenciaArchivo::class,
            'incidencia_id'
        );
    }
}