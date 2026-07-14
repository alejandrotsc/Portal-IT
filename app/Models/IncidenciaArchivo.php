<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;



class IncidenciaArchivo extends Model
{


    protected $fillable=[
        'incidencia_id',
        'archivo',
        'nombre_original',
        'tipo'
    ];



    public function incidencia()
    {
        return $this->belongsTo(
            Incidencia::class
        );
    }

}