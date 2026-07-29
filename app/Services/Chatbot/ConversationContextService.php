<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotConversation;

class ConversationContextService
{
    public function getRecent(
        ?int $userId,
        int $limit = 2
    ): array {
        if (! $userId) {
            return [];
        }

        /*
         * El límite representa conversaciones completas,
         * no mensajes individuales.
         */
        $limit = max(
            0,
            min($limit, 6)
        );

        if ($limit === 0) {
            return [];
        }

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
                static function (
                    ChatbotConversation $conversation
                ): array {
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
                                500
                            ),
                        ];
                    }

                    if ($assistantMessage !== '') {
                        $messages[] = [
                            'role' => 'assistant',
                            'content' => mb_substr(
                                $assistantMessage,
                                0,
                                700
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