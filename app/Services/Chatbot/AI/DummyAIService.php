<?php

namespace App\Services\Chatbot\AI;

class DummyAIService implements AIServiceInterface
{
    /*
    |--------------------------------------------------------------------------
    | Generar respuesta simulada
    |--------------------------------------------------------------------------
    |
    | Devuelve una respuesta predeterminada utilizada como implementación
    | básica del servicio de inteligencia artificial cuando no se requiere
    | realizar una consulta a un proveedor externo.
    |
    */

    public function ask(
        string $message,
        array $context = []
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Obtener usuario
        |--------------------------------------------------------------------------
        |
        | Recupera el nombre del usuario desde el contexto disponible o
        | utiliza un valor genérico cuando esta información no existe.
        |
        */

        $usuario = $context['usuario']
            ??
            'usuario';

        /*
        |--------------------------------------------------------------------------
        | Construir respuesta
        |--------------------------------------------------------------------------
        |
        | Genera un mensaje orientativo indicando las categorías de soporte
        | disponibles y solicita más información cuando no existe una
        | clasificación exacta para la consulta recibida.
        |
        */

        return 
            "Hola {$usuario}. "
            ."No encontré una categoría exacta para tu solicitud, "
            ."pero puedo ayudarte con problemas de equipos, "
            ."correo, sistemas, accesos o solicitudes TI.\n\n"
            ."Puedes explicarme un poco más el problema "
            ."o seleccionar una opción del menú.";
    }
}