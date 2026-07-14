<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudCompra extends Model
{
    protected $table = 'solicitud_compras';

    protected $fillable = [
        'memorando_id',
        'empresa',
        'tipo_compra',
        'motivo_compra',
        'proveedor',
        'razon_proveedor'
    ];

    public function memorando()
    {
        return $this->belongsTo(
            Memorando::class,
            'memorando_id'
        );
    }

    public function articulos()
    {
        return $this->hasMany(
            MemorandoArticulo::class,
            'memorando_id',
            'memorando_id'
        );
    }
}