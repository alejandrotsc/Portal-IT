<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $table = 'chatbot_conversations';

    protected $fillable = [
        'usuario_id',
        'mensaje',
        'intencion_detectada',
        'puntuacion',
        'respuesta',
        'es_util',
        'accion',
    ];

    protected $casts = [
        'puntuacion'=>'integer',
        'es_util'=>'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }

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