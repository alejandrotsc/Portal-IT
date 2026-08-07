<?php

namespace App\Services\Chatbot;

class IntentResult
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Inicializa el resultado del reconocimiento de intención junto con
    | su puntuación, nivel de confianza, palabras coincidentes y posibles
    | alternativas detectadas.
    |
    */

    public function __construct(
        public readonly string $intent,
        public readonly int $score = 0,
        public readonly array $matchedKeywords = [],
        public readonly float $confidence = 0,
        public readonly array $alternatives = [],
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Comparar intención
    |--------------------------------------------------------------------------
    |
    | Comprueba si la intención detectada coincide exactamente con la
    | intención proporcionada.
    |
    */

    public function is(
        string $intent
    ): bool {
        return $this->intent === $intent;
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar intención desconocida
    |--------------------------------------------------------------------------
    |
    | Determina si el reconocimiento no logró identificar una intención
    | conocida dentro de las reglas disponibles.
    |
    */

    public function isUnknown(): bool
    {
        return $this->intent === 'desconocido';
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar nivel de confianza
    |--------------------------------------------------------------------------
    |
    | Comprueba si el nivel de confianza obtenido alcanza o supera el
    | mínimo requerido para considerar confiable la intención detectada.
    |
    */

    public function isConfident(
        float $minimum = 0.70
    ): bool {
        return $this->confidence >= $minimum;
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener mejor alternativa
    |--------------------------------------------------------------------------
    |
    | Devuelve la primera alternativa disponible o null cuando no existen
    | otras intenciones candidatas.
    |
    */

    public function bestAlternative(): ?array
    {
        return $this->alternatives[0] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | Comprobar alternativas
    |--------------------------------------------------------------------------
    |
    | Determina si el reconocimiento produjo una o más intenciones
    | alternativas además de la seleccionada como principal.
    |
    */

    public function hasAlternative(): bool
    {
        return !empty($this->alternatives);
    }

    /*
    |--------------------------------------------------------------------------
    | Convertir resultado a arreglo
    |--------------------------------------------------------------------------
    |
    | Convierte el resultado de intención a una estructura de arreglo
    | reutilizable por respuestas JSON y otros componentes del chatbot.
    |
    */

    public function toArray(): array
    {
        return [
            'intent'=>$this->intent,
            'score'=>$this->score,
            'confidence'=>$this->confidence,
            'matchedKeywords'=>$this->matchedKeywords,
            'alternatives'=>$this->alternatives,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Crear resultado desconocido
    |--------------------------------------------------------------------------
    |
    | Genera una instancia predeterminada que representa una intención no
    | identificada y sin puntuación, confianza ni alternativas asociadas.
    |
    */

    public static function unknown(): self
    {
        return new self(
            intent:'desconocido',
            score:0,
            matchedKeywords:[],
            confidence:0,
            alternatives:[]
        );
    }
}