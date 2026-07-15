<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenciaArchivo extends Model
{

    protected $table = 'incidencia_archivos';


    protected $fillable = [

        'incidencia_id',

        'usuario_id',

        'nombre_original',

        'nombre_archivo',

        'ruta',

        'extension',

        'tamano',

        'texto_ocr'

    ];



    /*
    |--------------------------------------------------------------------------
    | Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'tamano' => 'integer'

    ];




    /*
    |--------------------------------------------------------------------------
    | Incidencia relacionada
    |--------------------------------------------------------------------------
    */

    public function incidencia()
    {
        return $this->belongsTo(
            Incidencia::class,
            'incidencia_id'
        );
    }





    /*
    |--------------------------------------------------------------------------
    | Usuario que subió el archivo
    |--------------------------------------------------------------------------
    */

    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }


}