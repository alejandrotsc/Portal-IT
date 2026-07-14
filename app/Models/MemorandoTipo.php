<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandoTipo extends Model
{
    protected $table = 'memorando_tipos';

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

    public function memorandos()
    {
        return $this->hasMany(
            Memorando::class,
            'tipo_id'
        );
    }

    public function getNombreVisualAttribute()
    {
        return match ($this->slug) {

            'autorizacion' => 'Pase mayor a 24 horas',

            'pase_temporal' => 'Pase menor a 24 horas',

            default => $this->nombre,

        };
    }
}