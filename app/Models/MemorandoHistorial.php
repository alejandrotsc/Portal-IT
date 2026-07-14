<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandoHistorial extends Model
{
    protected $table = 'memorando_historial';


    protected $fillable = [
        'memorando_id',
        'usuario_id',
        'estado_anterior',
        'estado_nuevo',
        'comentario'
    ];



    public function memorando()
    {
        return $this->belongsTo(
            Memorando::class,
            'memorando_id'
        );
    }



    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }
}