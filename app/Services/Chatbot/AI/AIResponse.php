<?php

declare(strict_types=1);

namespace App\Services\Chatbot\AI;

class AIResponse
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Inicializa la respuesta generada por el servicio de inteligencia
    | artificial junto con su categoría, nivel de confianza, acciones
    | rápidas y metadatos asociados.
    |
    */

    public function __construct(
        public readonly string $message,
        public readonly string $category = 'general',
        public readonly float $confidence = 0.0,
        public readonly array $quickActions = [],
        public readonly array $metadata = [],
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar respuesta disponible
    |--------------------------------------------------------------------------
    |
    | Determina si la respuesta contiene un mensaje utilizable después
    | de eliminar posibles espacios en blanco externos.
    |
    */

    public function hasResponse(): bool
    {
        return trim($this->message) !== '';
    }

    /*
    |--------------------------------------------------------------------------
    | Estado de la respuesta
    |--------------------------------------------------------------------------
    |
    | Proporciona métodos auxiliares para identificar el origen y las
    | condiciones especiales asociadas a la respuesta generada.
    |
    */

    public function isFromOllama(): bool
    {
        return $this->provider() === 'ollama';
    }

    public function isFallback(): bool
    {
        return $this->provider() === 'fallback';
    }

    public function isBusy(): bool
    {
        return $this->provider() === 'busy';
    }

    public function isReused(): bool
    {
        return (bool) $this->metadataValue(
            'reused',
            false
        );
    }

    public function isTruncated(): bool
    {
        return (bool) $this->metadataValue(
            'truncated',
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Proveedor de la respuesta
    |--------------------------------------------------------------------------
    |
    | Obtiene el identificador del proveedor que generó la respuesta,
    | siempre que exista un valor válido dentro de los metadatos.
    |
    */

    public function provider(): ?string
    {
        $provider = $this->metadataValue(
            'provider'
        );

        return is_string($provider)
            && $provider !== ''
                ? $provider
                : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Consultar metadatos
    |--------------------------------------------------------------------------
    |
    | Obtiene un valor específico almacenado dentro de los metadatos
    | de la respuesta o devuelve el valor predeterminado indicado.
    |
    */

    public function metadataValue(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->metadata[$key]
            ?? $default;
    }

    /*
    |--------------------------------------------------------------------------
    | Convertir respuesta a arreglo
    |--------------------------------------------------------------------------
    |
    | Convierte la respuesta de inteligencia artificial a una estructura
    | de arreglo compatible con respuestas JSON y otros procesos internos.
    |
    */

    public function toArray(): array
    {
        return [
            'message' =>
                $this->message,

            'category' =>
                $this->category,

            'confidence' =>
                $this->confidence,

            'quick_actions' =>
                $this->quickActions,

            'metadata' =>
                $this->metadata,
        ];
    }
}