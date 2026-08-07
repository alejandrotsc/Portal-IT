<?php

namespace App\Services\Chatbot;

class IntentRecognizer implements IntentRecognizerInterface
{
    /*
    |--------------------------------------------------------------------------
    | Reconocer intención
    |--------------------------------------------------------------------------
    |
    | Analiza el mensaje recibido y devuelve una estructura básica con
    | la intención detectada y una respuesta asociada.
    |
    */

    public function recognize(string $message)
    {
        return [
            'intent' => 'general',
            'response' => 'Estoy procesando tu solicitud.'
        ];
    }
}