<?php

namespace App\Services\Chatbot;

/*
|--------------------------------------------------------------------------
| Reconocimiento de intención
|--------------------------------------------------------------------------
|
| Define el contrato que debe cumplir cualquier motor encargado de
| analizar los mensajes del usuario y determinar su intención.
|
| Actualmente puede utilizarse un reconocedor basado en palabras clave,
| mientras que futuras implementaciones pueden apoyarse en inteligencia
| artificial sin modificar los componentes que dependen de esta interfaz.
|
| Esto permite intercambiar la implementación desde el Service Provider
| manteniendo estable el resto de la arquitectura del chatbot.
|
*/

interface IntentRecognizerInterface
{
    /*
    |--------------------------------------------------------------------------
    | Reconocer intención
    |--------------------------------------------------------------------------
    |
    | Analiza el mensaje proporcionado por el usuario y devuelve un
    | IntentResult con la intención detectada y sus datos asociados.
    |
    */

    public function recognize(string $message): IntentResult;
}