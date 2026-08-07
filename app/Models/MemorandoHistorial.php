<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandoHistorial extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar el historial de cambios
    | realizados sobre los memorandos dentro del Portal TI.
    |
    */

    protected $table = 'memorando_historial';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante el registro de un cambio en el historial del memorando.
    |
    */

    protected $fillable = [
        'memorando_id',
        'usuario_id',
        'estado_anterior',
        'estado_nuevo',
        'comentario'
    ];

    /*
    |--------------------------------------------------------------------------
    | Memorando relacionado
    |--------------------------------------------------------------------------
    |
    | Define la relación con el memorando al que pertenece el registro
    | almacenado dentro del historial de cambios.
    |
    */

    public function memorando()
    {
        return $this->belongsTo(
            Memorando::class,
            'memorando_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Usuario responsable
    |--------------------------------------------------------------------------
    |
    | Define la relación con el usuario que realizó el cambio registrado
    | dentro del historial del memorando.
    |
    */

    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }
}