<?php

namespace App\Services\Chatbot\AI;

interface AIServiceInterface
{
    public function ask(
        string $message,
        array $context = []
    ): AIResponse;

    public function stream(
        string $message,
        array $context,
        callable $onChunk
    ): AIResponse;

    /*
     * Precargar el modelo sin generar una respuesta.
     */
    public function warmUp(): bool;
}