<?php

namespace App\Services\Chatbot;

/**
 * Contrato para cualquier motor de reconocimiento de intención.
 *
 * Etapa 1 (actual): KeywordIntentRecognizer -> coincidencia de palabras clave.
 * Etapa futura: AiIntentRecognizer -> llamada a un modelo de lenguaje /
 * base de conocimiento, implementando esta misma interfaz.
 *
 * Gracias a esto, ChatbotController nunca necesita cambiar cuando se
 * incorpore IA: solo se reemplaza el binding en el Service Provider.
 */
interface IntentRecognizerInterface
{
    /**
     * Analiza el mensaje del usuario y devuelve la intención detectada.
     *
     * @param  string  $message  Texto escrito por el usuario.
     * @return IntentResult
     */
    public function recognize(string $message): IntentResult;
}