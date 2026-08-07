<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolioCounter extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los contadores empleados
    | en la generación consecutiva de folios dentro del Portal TI.
    |
    */

    protected $table = 'folio_counters';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva al
    | crear o actualizar un contador de folios.
    |
    */

    protected $fillable = [
        'prefijo',
        'ultimo_valor'
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente el último valor registrado a un número
    | entero al interactuar con el modelo.
    |
    */

    protected $casts = [
        'ultimo_valor' => 'integer'
    ];
}