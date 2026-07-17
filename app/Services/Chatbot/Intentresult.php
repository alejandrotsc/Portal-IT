<?php

namespace App\Services\Chatbot;

/**
 * Resultado de clasificar un mensaje: qué intención se detectó,
 * con qué confianza (score) y cuáles palabras clave "dispararon" la coincidencia.
 * Ese detalle de debug es útil ahora para calibrar keywords, y luego para
 * comparar el desempeño del recognizer por keywords vs. el basado en IA.
 */
class IntentResult
{
    public function __construct(
        public readonly string $intent,
        public readonly int $score = 0,
        public readonly array $matchedKeywords = [],
    ) {
    }

    public function is(string $intent): bool
    {
        return $this->intent === $intent;
    }
}