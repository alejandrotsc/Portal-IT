<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotConversation;

class ConversationContextService
{
    /*
    |--------------------------------------------------------------------------
    | Límite máximo de conversaciones
    |--------------------------------------------------------------------------
    |
    | Define un límite absoluto de seguridad para la cantidad de
    | conversaciones que pueden recuperarse desde la base de datos,
    | independientemente de la configuración solicitada.
    |
    */

    private const MAX_CONVERSATIONS = 6;

    /*
    |--------------------------------------------------------------------------
    | Obtener historial reciente
    |--------------------------------------------------------------------------
    |
    | Recupera las conversaciones recientes del usuario y las transforma
    | al formato de mensajes utilizado como contexto por el servicio de IA.
    |
    */

    public function getRecent(
        ?int $userId,
        ?int $limit = null
    ): array {
        if (! $userId) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Resolver límite de conversaciones
        |--------------------------------------------------------------------------
        |
        | Utiliza por defecto el mismo límite configurado para el historial
        | enviado a Ollama, manteniendo una única fuente de configuración.
        |
        */

        $limit ??= (int) config(
            'chatbot.ai.history_limit',
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Aplicar límite de seguridad
        |--------------------------------------------------------------------------
        |
        | El valor representa conversaciones completas y se restringe al
        | máximo permitido por el servicio.
        |
        */

        $limit = max(
            0,
            min($limit, self::MAX_CONVERSATIONS)
        );

        if ($limit === 0) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Resolver longitud máxima por mensaje
        |--------------------------------------------------------------------------
        |
        | Utiliza la misma longitud configurada para el historial de Ollama
        | para evitar aplicar límites diferentes durante el procesamiento.
        |
        */

        $messageLength = max(
            50,
            (int) config(
                'chatbot.ai.history_message_length',
                300
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Recuperar conversaciones
        |--------------------------------------------------------------------------
        |
        | Obtiene únicamente conversaciones que contienen respuesta y excluye
        | las acciones internas generadas por los flujos interactivos.
        |
        */

        return ChatbotConversation::query()
            ->select([
                'mensaje',
                'respuesta',
            ])
            ->where(
                'usuario_id',
                $userId
            )
            ->whereNotNull(
                'respuesta'
            )
            ->where(
                'mensaje',
                'not like',
                '[Acción]%'
            )
            ->latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->flatMap(
                function (
                    ChatbotConversation $conversation
                ) use ($messageLength): array {
                    $messages = [];

                    /*
                    |--------------------------------------------------------------------------
                    | Preparar mensaje del usuario
                    |--------------------------------------------------------------------------
                    */

                    $userMessage = trim(
                        (string) $conversation->mensaje
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Preparar respuesta del asistente
                    |--------------------------------------------------------------------------
                    */

                    $assistantMessage = trim(
                        (string) $conversation->respuesta
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Agregar mensaje del usuario
                    |--------------------------------------------------------------------------
                    */

                    if ($userMessage !== '') {
                        $messages[] = [
                            'role' => 'user',
                            'content' => mb_substr(
                                $userMessage,
                                0,
                                $messageLength
                            ),
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Agregar respuesta del asistente
                    |--------------------------------------------------------------------------
                    */

                    if ($assistantMessage !== '') {
                        $messages[] = [
                            'role' => 'assistant',
                            'content' => mb_substr(
                                $assistantMessage,
                                0,
                                $messageLength
                            ),
                        ];
                    }

                    return $messages;
                }
            )
            ->values()
            ->all();
    }
}