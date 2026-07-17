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

        'es_util' => 'boolean',

        'puntuacion' => 'integer',

    ];



    public function usuario(): BelongsTo
    {

        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );

    }

}