<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandoTipo extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los diferentes tipos de
    | memorandos disponibles dentro del Portal TI.
    |
    */

    protected $table = 'memorando_tipos';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un tipo de memorando.
    |
    */

    protected $fillable = [
        'nombre',
        'slug',
        'requiere_folio',
        'creado_por_rol',
        'requiere_aprobacion',
        'formulario',
        'plantilla',
        'activo'
    ];

    /*
    |--------------------------------------------------------------------------
    | Memorandos relacionados
    |--------------------------------------------------------------------------
    |
    | Define la relación con los memorandos que pertenecen al tipo
    | correspondiente.
    |
    */

    public function memorandos()
    {
        return $this->hasMany(
            Memorando::class,
            'tipo_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Nombre visual
    |--------------------------------------------------------------------------
    |
    | Devuelve el nombre que debe mostrarse en la interfaz según el tipo
    | de memorando, manteniendo el nombre original para los demás casos.
    |
    */

    public function getNombreVisualAttribute()
    {
        return match ($this->slug) {
            'autorizacion' => 'Pase mayor a 24 horas',

            'pase_temporal' => 'Pase menor a 24 horas',

            default => $this->nombre,
        };
    }
}