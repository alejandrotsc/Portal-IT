<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotConversation extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar las conversaciones y
    | registros generados durante la interacción con el chatbot.
    |
    */

    protected $table = 'chatbot_conversations';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva al
    | crear o actualizar un registro de conversación del chatbot.
    |
    */

    protected $fillable = [
        'usuario_id',
        'mensaje',
        'intencion_detectada',
        'puntuacion',
        'respuesta',
        'es_util',
        'accion',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente la puntuación a entero y el indicador
    | de utilidad de la respuesta a un valor booleano.
    |
    */

    protected $casts = [
        'puntuacion'=>'integer',
        'es_util'=>'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    |
    | Define la relación entre la conversación registrada y el usuario
    | que realizó la interacción con el chatbot.
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
    | Conversaciones recientes por usuario
    |--------------------------------------------------------------------------
    |
    | Filtra las conversaciones pertenecientes a un usuario específico
    | y las ordena desde el registro más reciente según su identificador.
    |
    */

    public function scopeRecentFor(
        $query,
        int $userId
    )
    {
        return $query
            ->where(
                'usuario_id',
                $userId
            )
            ->latest('id');
    }
}