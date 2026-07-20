<?php

namespace App\Services\Chatbot;

class IntentResult
{
    public function __construct(
        public readonly string $intent,
        public readonly int $score = 0,
        public readonly array $matchedKeywords = [],
        public readonly float $confidence = 0,
        public readonly array $alternatives = [],
    ) {}

    public function is(
        string $intent
    ): bool {
        return $this->intent === $intent;
    }

    public function isUnknown(): bool
    {
        return $this->intent === 'desconocido';
    }

    public function isConfident(
        float $minimum = 0.70
    ): bool {
        return $this->confidence >= $minimum;
    }

    public function bestAlternative(): ?array
    {
        return $this->alternatives[0] ?? null;
    }

    public function hasAlternative(): bool
    {
        return !empty($this->alternatives);
    }

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