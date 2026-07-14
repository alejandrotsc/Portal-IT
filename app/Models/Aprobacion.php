<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aprobacion extends Model
{
    protected $table = 'aprobaciones';


    protected $fillable = [
        'memorando_id',
        'nombre_aprobador',
        'cargo_aprobador',
        'estado',
        'comentario',
        'fecha_aprobacion',
        'registrado_por'
    ];


    protected $casts = [
        'fecha_aprobacion' => 'datetime'
    ];


    public function memorando()
    {
        return $this->belongsTo(
            Memorando::class,
            'memorando_id'
        );
    }
}