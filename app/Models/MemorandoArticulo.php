<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandoArticulo extends Model
{
    protected $table = 'memorando_articulos';


    protected $fillable = [
        'memorando_id',
        'codigo',
        'descripcion',
        'unidad',
        'cantidad'
    ];


    public function memorando()
    {
        return $this->belongsTo(
            Memorando::class,
            'memorando_id'
        );
    }
}