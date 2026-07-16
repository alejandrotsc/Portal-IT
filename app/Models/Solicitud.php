<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{

    protected $table = 'solicitudes';


    protected $fillable = [

        'folio',
        'usuario_id',
        'categoria',
        'asunto',
        'descripcion',
        'datos_extra',
        'estado',
        'correo_enviado',
        'correo_enviado_at'

    ];


    protected $casts = [

        'datos_extra' => 'array',
        'correo_enviado' => 'boolean',
        'correo_enviado_at' => 'datetime'

    ];



    public function usuario()
    {

        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );

    }

}