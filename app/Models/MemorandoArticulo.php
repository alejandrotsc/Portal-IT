<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandoArticulo extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los artículos vinculados
    | a los memorandos generados dentro del Portal TI.
    |
    */

    protected $table = 'memorando_articulos';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un artículo de memorando.
    |
    */

    protected $fillable = [
        'memorando_id',
        'codigo',
        'descripcion',
        'unidad',
        'cantidad'
    ];

    /*
    |--------------------------------------------------------------------------
    | Memorando relacionado
    |--------------------------------------------------------------------------
    |
    | Define la relación con el memorando al que pertenece el artículo
    | registrado dentro del detalle de la gestión.
    |
    */

    public function memorando()
    {
        return $this->belongsTo(
            Memorando::class,
            'memorando_id'
        );
    }
}