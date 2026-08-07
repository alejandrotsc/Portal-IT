<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudCompra extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar la información específica
    | de las solicitudes de compra vinculadas a los memorandos.
    |
    */

    protected $table = 'solicitud_compras';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de una solicitud de compra.
    |
    */

    protected $fillable = [
        'memorando_id',
        'empresa',
        'tipo_compra',
        'motivo_compra',
        'proveedor',
        'razon_proveedor'
    ];

    /*
    |--------------------------------------------------------------------------
    | Memorando relacionado
    |--------------------------------------------------------------------------
    |
    | Define la relación con el memorando al que pertenece la solicitud
    | de compra registrada dentro del Portal TI.
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
    | Artículos relacionados
    |--------------------------------------------------------------------------
    |
    | Define la relación con los artículos asociados al mismo memorando
    | utilizado por la solicitud de compra.
    |
    */

    public function articulos()
    {
        return $this->hasMany(
            MemorandoArticulo::class,
            'memorando_id',
            'memorando_id'
        );
    }
}