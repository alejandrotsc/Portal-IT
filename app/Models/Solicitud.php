<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitud extends Model
{
    protected $table = 'solicitudes';


    /*
    |--------------------------------------------------------------------------
    | Estados
    |--------------------------------------------------------------------------
    */

    public const ESTADO_PENDIENTE =
        'pendiente';

    public const ESTADO_FINALIZADA =
        'finalizada';

    public const ESTADO_CANCELADA =
        'cancelada';


    protected $fillable = [
        'folio',
        'usuario_id',
        'categoria',
        'asunto',
        'descripcion',
        'datos_extra',
        'estado',
        'correo_enviado',
        'correo_enviado_at',
    ];


    protected $casts = [
        'datos_extra' => 'array',

        'correo_enviado' => 'boolean',

        'correo_enviado_at' => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Estado
    |--------------------------------------------------------------------------
    */

    public function estaPendiente(): bool
    {
        return $this->estado
            === self::ESTADO_PENDIENTE;
    }


    public function estaFinalizada(): bool
    {
        return $this->estado
            === self::ESTADO_FINALIZADA;
    }


    public function estaCancelada(): bool
    {
        return $this->estado
            === self::ESTADO_CANCELADA;
    }
}