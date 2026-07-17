<?php

namespace App\Services\Chatbot\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaAIService implements AIServiceInterface
{
    public function __construct(
        private readonly PromptBuilder $promptBuilder
    ) {}

    public function ask(
        string $message,
        array $context = []
    ): AIResponse {
        try {
            $prompt = $this->promptBuilder->build(
                $message,
                $context
            );

            $response = Http::timeout(
                config('chatbot.ai.timeout',60)
            )->post(
                config(
                    'chatbot.ai.url',
                    'http://127.0.0.1:11434/api/generate'
                ),
                [
                    'model'=>config(
                        'chatbot.ai.model',
                        'llama3.2'
                    ),
                    'prompt'=>$prompt,
                    'stream'=>false,
                    'options'=>[
                        'temperature'=>0.2,
                    ],
                ]
            );

            if(!$response->successful()){
                Log::warning('Ollama respondió con error',[
                    'status'=>$response->status(),
                    'body'=>$response->body(),
                ]);

                return $this->fallbackResponse();
            }

            $data = $response->json();

            $answer = trim(
                $data['response'] ?? ''
            );

            if($answer === ''){
                return $this->fallbackResponse();
            }

            return new AIResponse(
                message:$answer,
                category:'ti',
                confidence:0.80,
                metadata:[
                    'provider'=>'ollama',
                    'model'=>config(
                        'chatbot.ai.model',
                        'llama3.2'
                    ),
                ]
            );

        }catch(\Throwable $e){
            Log::error('Error comunicando con Ollama',[
                'error'=>$e->getMessage(),
                'file'=>$e->getFile(),
                'line'=>$e->getLine(),
            ]);

            return $this->fallbackResponse();
        }
    }

    private function fallbackResponse(): AIResponse
    {
        return new AIResponse(
            message:
                'No pude consultar el asistente técnico en este momento. '
                .'Puedes intentar nuevamente o registrar una incidencia.',
            category:'system',
            confidence:0,
            metadata:[
                'provider'=>'fallback',
            ]
        );
    }
}