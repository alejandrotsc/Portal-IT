<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandoArchivo extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los archivos vinculados
    | a los memorandos generados dentro del Portal TI.
    |
    */

    protected $table = 'memorando_archivos';

    /*
    |--------------------------------------------------------------------------
    | Marcas de tiempo
    |--------------------------------------------------------------------------
    |
    | Deshabilita la gestión automática de las columnas created_at y
    | updated_at para este modelo.
    |
    */

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un archivo de memorando.
    |
    */

    protected $fillable = [
        'memorando_id',
        'tipo_archivo',
        'nombre_archivo',
        'ruta_archivo',
        'url_sharepoint',
        'cargado_por'
    ];

    /*
    |--------------------------------------------------------------------------
    | Memorando relacionado
    |--------------------------------------------------------------------------
    |
    | Define la relación con el memorando al que pertenece el archivo
    | almacenado o vinculado.
    |
    */

    public function memorando()
    {
        return $this->belongsTo(
            Memorando::class,
            'memorando_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Usuario que cargó el archivo
    |--------------------------------------------------------------------------
    |
    | Define la relación con el usuario responsable de cargar o registrar
    | el archivo asociado al memorando.
    |
    */

    public function cargador()
    {
        return $this->belongsTo(
            Usuario::class,
            'cargado_por'
        );
    }
}