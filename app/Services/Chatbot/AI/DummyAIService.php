<?php

namespace App\Services\Chatbot\AI;


class DummyAIService implements AIServiceInterface
{


    public function ask(

        string $message,

        array $context = []

    ): string {


        $usuario = $context['usuario']
            ??
            'usuario';



        return 
            "Hola {$usuario}. "
            ."No encontré una categoría exacta para tu solicitud, "
            ."pero puedo ayudarte con problemas de equipos, "
            ."correo, sistemas, accesos o solicitudes TI.\n\n"
            ."Puedes explicarme un poco más el problema "
            ."o seleccionar una opción del menú.";


    }


}