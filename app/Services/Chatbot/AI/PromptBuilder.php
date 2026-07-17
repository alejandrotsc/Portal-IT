<?php

namespace App\Services\Chatbot\AI;


/**
 * Construye los prompts enviados al modelo de IA.
 *
 * Mantiene las reglas del asistente separadas
 * del proveedor de IA.
 */
class PromptBuilder
{


    /**
     * Construye el prompt completo.
     *
     * @param string $message
     * Mensaje del usuario.
     *
     * @param array $context
     * Información adicional disponible.
     *
     * @return string
     */
    public function build(

        string $message,

        array $context = []

    ): string {


        $system = $this->systemPrompt();


        $contextText = $this->buildContext(
            $context
        );



        return <<<PROMPT

{$system}


CONTEXTO DISPONIBLE:

{$contextText}


CONSULTA DEL USUARIO:

{$message}


INSTRUCCIONES FINALES:

- Responde únicamente la consulta relacionada con TI.
- No inventes rutas, procesos o permisos que no existan.
- Si no tienes suficiente información, solicita más detalles.
- Utiliza lenguaje sencillo para usuarios no técnicos.
- No menciones que eres un modelo de inteligencia artificial.
- No respondas temas fuera del área tecnológica.


RESPUESTA:

PROMPT;

    }





    /**
     * Reglas principales del asistente.
     */
    private function systemPrompt(): string
    {

        return <<<TEXT

Eres el asistente virtual del Portal TI.

Tu función es ayudar únicamente con soporte tecnológico
para usuarios de la organización.


ÁREAS PERMITIDAS:

- Computadoras y laptops.
- Windows.
- Hardware.
- Software empresarial.
- Microsoft Office.
- Outlook y correo corporativo.
- Impresoras.
- Redes e internet.
- WiFi.
- VPN.
- Accesos tecnológicos.
- Problemas comunes de usuarios.
- Uso del Portal TI.


NO PUEDES RESPONDER:

- Política.
- Deportes.
- Medicina.
- Religión.
- Cocina.
- Entretenimiento.
- Desarrollo de software no relacionado con soporte al usuario.
- Temas personales.


Si el usuario pregunta algo fuera de soporte tecnológico,
responde:

"Lo siento, únicamente puedo ayudarte con temas relacionados al soporte tecnológico del Portal TI."


Tu objetivo es orientar al usuario antes de crear una incidencia,
proporcionando pasos simples y seguros.

Nunca solicites contraseñas.
Nunca solicites información sensible.

TEXT;

    }





    /**
     * Convierte contexto adicional a texto legible.
     */
    private function buildContext(array $context): string
    {

        if(empty($context)){

            return 'Sin contexto adicional.';

        }


        $lines = [];


        foreach($context as $key => $value){


            if(is_array($value)){


                $value = implode(
                    ', ',
                    $value
                );


            }


            $lines[] = "- {$key}: {$value}";


        }



        return implode(
            PHP_EOL,
            $lines
        );

    }


}