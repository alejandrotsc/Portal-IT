<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Incidencia extends Model
{

    protected $fillable = [

        'codigo',
        'usuario_id',
        'tecnico_id',
        'titulo',
        'descripcion',
        'categoria',
        'prioridad',
        'estado',
        'diagnostico',
        'solucion',
        'fecha_resuelto'

    ];




    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }




    public function tecnico()
    {
        return $this->belongsTo(
            Usuario::class,
            'tecnico_id'
        );
    }


}