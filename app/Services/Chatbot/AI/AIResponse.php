<?php

namespace App\Services\Chatbot\AI;


/**
 * Representa una respuesta generada por el motor IA.
 */
class AIResponse
{


    public function __construct(


        /**
         * Texto que verá el usuario.
         */
        public readonly string $message,



        /**
         * Categoría detectada.
         */
        public readonly string $category = 'general',




        /**
         * Confianza estimada.
         */
        public readonly float $confidence = 0.0,





        /**
         * Acciones sugeridas.
         */
        public readonly array $quickActions = [],




        /**
         * Metadata del proveedor.
         */
        public readonly array $metadata = [],


    ) {}









    /**
     * Verifica si existe respuesta válida.
     */
    public function hasResponse(): bool
    {

        return trim($this->message) !== '';

    }









    /**
     * Convertir a array.
     */
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