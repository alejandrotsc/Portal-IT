<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{

    protected $table = 'incidencias';


    protected $fillable = [

        'codigo',

        'usuario_id',

        'titulo',

        'descripcion',

        'tiempo_problema',

        'afectacion',

        'equipo',

        'ubicacion',

        'estado',

        'prioridad',

        'correo_enviado',

        'fecha_envio_correo'

    ];



    protected $casts = [

        'correo_enviado' => 'boolean',

        'fecha_envio_correo' => 'datetime',

    ];



    /*
    |--------------------------------------------------------------------------
    | Usuario creador
    |--------------------------------------------------------------------------
    */

    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }





    /*
    |--------------------------------------------------------------------------
    | Archivos adjuntos
    |--------------------------------------------------------------------------
    */

    public function archivos()
    {
        return $this->hasMany(
            IncidenciaArchivo::class,
            'incidencia_id'
        );
    }


}