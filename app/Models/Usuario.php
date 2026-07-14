<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';


    protected $fillable = [
        'nombre',
        'username',
        'correo',
        'password',
        'rol_id',
        'activo'
    ];


    protected $hidden = [
        'password',
        'remember_token'
    ];


    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean'
        ];
    }



    public function rol()
    {
        return $this->belongsTo(
            Rol::class,
            'rol_id'
        );
    }



    public function memorandos()
    {
        return $this->hasMany(
            Memorando::class,
            'solicitante_id'
        );
    }



    public function historial()
    {
        return $this->hasMany(
            MemorandoHistorial::class,
            'usuario_id'
        );
    }
}