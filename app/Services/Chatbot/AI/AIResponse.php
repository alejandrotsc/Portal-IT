<?php

namespace App\Services\Chatbot\AI;

class AIResponse
{
    public function __construct(
        public readonly string $message,
        public readonly string $category = 'general',
        public readonly float $confidence = 0.0,
        public readonly array $quickActions = [],
        public readonly array $metadata = [],
    ) {}

    public function hasResponse(): bool
    {
        return trim($this->message) !== '';
    }

    public function toArray(): array
    {
        return [
            'message'=>$this->message,
            'category'=>$this->category,
            'confidence'=>$this->confidence,
            'quick_actions'=>$this->quickActions,
            'metadata'=>$this->metadata,
        ];
    }
}