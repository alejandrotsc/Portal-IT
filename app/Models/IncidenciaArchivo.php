<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaArchivo extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los archivos y evidencias
    | adjuntados a las incidencias registradas dentro del Portal TI.
    |
    */

    protected $table =
        'incidencia_archivos';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un archivo de incidencia.
    |
    */

    protected $fillable = [
        'incidencia_id',
        'usuario_id',
        'nombre_original',
        'nombre_archivo',
        'ruta',
        'extension',
        'tamano',
        'texto_ocr',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente el tamaño almacenado del archivo a un
    | valor entero al interactuar con el modelo.
    |
    */

    protected $casts = [
        'tamano' =>
            'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Incidencia relacionada
    |--------------------------------------------------------------------------
    |
    | Define la relación con la incidencia a la que pertenece el archivo
    | o evidencia almacenada.
    |
    */

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(
            Incidencia::class,
            'incidencia_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Usuario que subió el archivo
    |--------------------------------------------------------------------------
    |
    | Define la relación con el usuario responsable de adjuntar el
    | archivo a la incidencia.
    |
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }
}