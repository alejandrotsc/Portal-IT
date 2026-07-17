<?php

namespace App\Services\Chatbot;

class KeywordIntentRecognizer implements IntentRecognizerInterface
{
    public function recognize(string $message): IntentResult
    {
        $normalized = $this->normalize($message);
        $keywordSets = config('chatbot.keywords', []);
        $minScore = (int) config('chatbot.min_score', 1);

        $best = new IntentResult('desconocido', 0, []);

        foreach ($keywordSets as $intent => $keywords) {
            $matched = [];

            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $this->normalize($keyword))) {
                    $matched[] = $keyword;
                }
            }

            $score = count($matched);

            if ($score > $best->score) {
                $best = new IntentResult($intent, $score, $matched);
            }
        }

        // "consultar_estado" y "saludo" compiten con las intenciones de gestión;
        // si el usuario dice algo como "hola, necesito reportar una falla",
        // preferimos la intención de gestión sobre el saludo genérico.
        if ($best->is('saludo') && $best->score <= 1) {
            return new IntentResult('saludo', $best->score, $best->matchedKeywords);
        }

        if ($best->score < $minScore) {
            return new IntentResult('desconocido', $best->score, $best->matchedKeywords);
        }

        return $best;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));

        $unwanted = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n'];
        $text = strtr($text, $unwanted);

        // colapsa espacios múltiples
        return preg_replace('/\s+/', ' ', $text) ?? $text;
    }
}