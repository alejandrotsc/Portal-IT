<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotConversation;

class ConversationContextService
{
    /*
     * Techo de seguridad absoluto para el número de conversaciones a
     * traer de la base de datos, independientemente de lo que pida el
     * llamador o de config(). Es una cota de protección, no un valor
     * de ajuste de comportamiento.
     */
    private const MAX_CONVERSATIONS = 6;

    public function getRecent(
        ?int $userId,
        ?int $limit = null
    ): array {
        if (! $userId) {
            return [];
        }

        /*
         * Por defecto se usa el mismo límite de historial configurado
         * para Ollama (chatbot.ai.history_limit), en vez de un valor
         * fijo propio. Así hay una sola fuente de verdad para cuánto
         * historial realmente se necesita, y no se recupera ni se
         * arma en PHP más contexto del que luego se enviará al modelo.
         */
        $limit ??= (int) config(
            'chatbot.ai.history_limit',
            2
        );

        /*
         * El límite representa conversaciones completas,
         * no mensajes individuales.
         */
        $limit = max(
            0,
            min($limit, self::MAX_CONVERSATIONS)
        );

        if ($limit === 0) {
            return [];
        }

        /*
         * Mismo límite de longitud por mensaje que usa Ollama para el
         * historial, evitando truncar dos veces con valores distintos.
         */
        $messageLength = max(
            50,
            (int) config(
                'chatbot.ai.history_message_length',
                300
            )
        );

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
            /*
             * No enviar al modelo acciones internas de los flows.
             */
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

                    $userMessage = trim(
                        (string) $conversation->mensaje
                    );

                    $assistantMessage = trim(
                        (string) $conversation->respuesta
                    );

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