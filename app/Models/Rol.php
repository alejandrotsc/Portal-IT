<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los roles disponibles
    | dentro del Portal TI.
    |
    */

    protected $table = 'roles';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un rol.
    |
    */

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    /*
    |--------------------------------------------------------------------------
    | Usuarios relacionados
    |--------------------------------------------------------------------------
    |
    | Define la relación con los usuarios que tienen asignado este rol
    | dentro del sistema.
    |
    */

    public function usuarios()
    {
        return $this->hasMany(
            Usuario::class,
            'rol_id'
        );
    }
}