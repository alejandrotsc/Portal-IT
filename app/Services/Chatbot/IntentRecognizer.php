<?php

namespace App\Services\Chatbot;

class IntentRecognizer implements IntentRecognizerInterface
{
    public function recognize(string $message)
    {
        return [
            'intent' => 'general',
            'response' => 'Estoy procesando tu solicitud.'
        ];
    }
}