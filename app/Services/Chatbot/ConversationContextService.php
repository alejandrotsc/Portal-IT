<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotConversation;

class ConversationContextService
{
    public function getRecent(
        ?int $userId,
        int $limit = 6
    ): array {
        if(!$userId){
            return [];
        }

        $limit = max(
            1,
            min($limit, 20)
        );

        return ChatbotConversation::query()
            ->where(
                'usuario_id',
                $userId
            )
            ->whereNotNull(
                'respuesta'
            )
            ->latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->flatMap(
                function(
                    ChatbotConversation $conversation
                ): array {
                    $messages = [];

                    $userMessage =
                        trim(
                            (string) $conversation->mensaje
                        );

                    $assistantMessage =
                        trim(
                            (string) $conversation->respuesta
                        );

                    if($userMessage !== ''){
                        $messages[] = [
                            'role'=>'user',
                            'content'=>$userMessage,
                        ];
                    }

                    if($assistantMessage !== ''){
                        $messages[] = [
                            'role'=>'assistant',
                            'content'=>$assistantMessage,
                        ];
                    }

                    return $messages;
                }
            )
            ->values()
            ->all();
    }
}
