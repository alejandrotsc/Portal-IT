<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitud extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar las solicitudes registradas
    | por los usuarios dentro del Portal TI.
    |
    */

    protected $table = 'solicitudes';

    /*
    |--------------------------------------------------------------------------
    | Estados
    |--------------------------------------------------------------------------
    |
    | Define los estados disponibles para representar el ciclo de vida
    | de una solicitud dentro del sistema.
    |
    */

    public const ESTADO_PENDIENTE =
        'pendiente';

    public const ESTADO_FINALIZADA =
        'finalizada';

    public const ESTADO_CANCELADA =
        'cancelada';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de una solicitud.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente los datos adicionales a un arreglo, el
    | estado de envío del correo a booleano y su fecha a un objeto de
    | fecha y hora.
    |
    */

    protected $casts = [
        'datos_extra' => 'array',

        'correo_enviado' => 'boolean',

        'correo_enviado_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Usuario relacionado
    |--------------------------------------------------------------------------
    |
    | Define la relación con el usuario que registró originalmente
    | la solicitud dentro del Portal TI.
    |
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
    | Consultar estado
    |--------------------------------------------------------------------------
    |
    | Proporciona métodos auxiliares para determinar rápidamente el
    | estado actual en el que se encuentra una solicitud.
    |
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