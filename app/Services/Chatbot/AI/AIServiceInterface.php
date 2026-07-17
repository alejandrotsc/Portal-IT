<?php

namespace App\Services\Chatbot\AI;


/**
 * Contrato para proveedores de inteligencia artificial.
 *
 * Puede implementarse con:
 *
 * - Ollama
 * - OpenAI
 * - Azure OpenAI
 * - Otros proveedores
 *
 * El resto del chatbot no depende
 * del proveedor utilizado.
 */
interface AIServiceInterface
{


    /**
     * Procesa una consulta mediante IA.
     *
     * @param string $message
     * Mensaje del usuario.
     *
     * @param array $context
     * Información adicional del usuario,
     * rol, sistemas disponibles, etc.
     *
     * @return AIResponse
     */
    public function ask(

        string $message,

        array $context = []

    ): AIResponse;


}