<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandoArchivo extends Model
{
    protected $table = 'memorando_archivos';

    public $timestamps = false;

    protected $fillable = [
        'memorando_id',
        'tipo_archivo',
        'nombre_archivo',
        'ruta_archivo',
        'url_sharepoint',
        'cargado_por'
    ];


    public function memorando()
    {
        return $this->belongsTo(
            Memorando::class,
            'memorando_id'
        );
    }


    public function cargador()
    {
        return $this->belongsTo(
            Usuario::class,
            'cargado_por'
        );
    }
}