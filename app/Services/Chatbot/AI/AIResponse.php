<?php

declare(strict_types=1);

namespace App\Services\Chatbot\AI;

class AIResponse
{
    public function __construct(
        public readonly string $message,
        public readonly string $category = 'general',
        public readonly float $confidence = 0.0,
        public readonly array $quickActions = [],
        public readonly array $metadata = [],
    ) {
    }

    /**
     * Indica si la IA devolvió un mensaje utilizable.
     */
    public function hasResponse(): bool
    {
        return trim($this->message) !== '';
    }

    /*
    |--------------------------------------------------------------------------
    | Estado de la respuesta
    |--------------------------------------------------------------------------
    */

    /**
     * Indica si la respuesta fue generada por Ollama.
     */
    public function isFromOllama(): bool
    {
        return $this->provider() === 'ollama';
    }

    /**
     * Indica si se utilizó una respuesta alternativa.
     */
    public function isFallback(): bool
    {
        return $this->provider() === 'fallback';
    }

    /**
     * Indica si el proveedor estaba ocupado.
     */
    public function isBusy(): bool
    {
        return $this->provider() === 'busy';
    }

    /**
     * Indica si se reutilizó una respuesta anterior.
     */
    public function isReused(): bool
    {
        return (bool) $this->metadataValue('reused', false);
    }

    /**
     * Indica si Ollama terminó la generación porque alcanzó
     * el límite configurado de tokens.
     */
    public function isTruncated(): bool
    {
        return (bool) $this->metadataValue('truncated', false);
    }

    /**
     * Obtiene el proveedor que generó la respuesta.
     */
    public function provider(): ?string
    {
        $provider = $this->metadataValue('provider');

        return is_string($provider) && $provider !== ''
            ? $provider
            : null;
    }

    /**
     * Obtiene un valor de los metadatos.
     */
    public function metadataValue(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Convierte la respuesta a un arreglo compatible
     * con respuestas JSON.
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'category' => $this->category,
            'confidence' => $this->confidence,
            'quick_actions' => $this->quickActions,
            'metadata' => $this->metadata,
        ];
    }
}