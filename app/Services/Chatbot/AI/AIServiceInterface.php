<?php

namespace App\Services\Chatbot\AI;

interface AIServiceInterface
{
    /*
    |--------------------------------------------------------------------------
    | Consultar servicio de inteligencia artificial
    |--------------------------------------------------------------------------
    |
    | Envía un mensaje al proveedor de inteligencia artificial junto con
    | el contexto disponible y devuelve una respuesta normalizada.
    |
    */

    public function ask(
        string $message,
        array $context = []
    ): AIResponse;

    /*
    |--------------------------------------------------------------------------
    | Generar respuesta por streaming
    |--------------------------------------------------------------------------
    |
    | Procesa la consulta de forma incremental y ejecuta el callback
    | proporcionado cada vez que se recibe un nuevo fragmento.
    |
    */

    public function stream(
        string $message,
        array $context,
        callable $onChunk
    ): AIResponse;

    /*
    |--------------------------------------------------------------------------
    | Precargar modelo
    |--------------------------------------------------------------------------
    |
    | Inicializa o mantiene preparado el modelo de inteligencia artificial
    | sin generar una respuesta para el usuario.
    |
    */

    public function warmUp(): bool;
}