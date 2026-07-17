<?php

namespace App\Services\Chatbot;


/**
 * Resultado del análisis de intención del chatbot.
 *
 * Contiene:
 *
 * - intención detectada
 * - puntuación obtenida
 * - palabras coincidentes
 * - nivel de confianza
 * - alternativas posibles
 */
class IntentResult
{


    public function __construct(

        public readonly string $intent,

        public readonly int $score = 0,

        public readonly array $matchedKeywords = [],

        public readonly float $confidence = 0,

        public readonly array $alternatives = [],

    ) {}








    /**
     * Verifica una intención específica.
     */
    public function is(
        string $intent
    ): bool {

        return $this->intent === $intent;

    }








    /**
     * Verifica si no pudo identificar intención.
     */
    public function isUnknown(): bool
    {

        return $this->intent === 'desconocido';

    }








    /**
     * Determina si la intención tiene confianza suficiente.
     */
    public function isConfident(
        float $minimum = 0.70
    ): bool {

        return $this->confidence >= $minimum;

    }








    /**
     * Devuelve la alternativa más probable.
     */
    public function bestAlternative(): ?array
    {

        return $this->alternatives[0] ?? null;

    }








    /**
     * Indica si existen alternativas.
     */
    public function hasAlternative(): bool
    {

        return !empty($this->alternatives);

    }








    /**
     * Convertir a array.
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








    /**
     * Crear resultado vacío.
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