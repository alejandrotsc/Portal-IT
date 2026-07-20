<?php

namespace App\Services\Chatbot\AI;

class PromptBuilder
{
    /*
    |--------------------------------------------------------------------------
    | Construir prompt completo
    |--------------------------------------------------------------------------
    |
    | Este método se conserva por compatibilidad.
    | OllamaAIService utiliza principalmente systemPrompt().
    |
    */

    public function build(
        string $message,
        array $context = []
    ): string {
        $message = trim($message);

        return $this->systemPrompt($context)
            .PHP_EOL
            .PHP_EOL
            .'Usuario: '
            .$message;
    }

    /*
    |--------------------------------------------------------------------------
    | Prompt optimizado
    |--------------------------------------------------------------------------
    |
    | El historial no se agrega aquí porque OllamaAIService ya lo envía
    | correctamente como mensajes de tipo user y assistant.
    |
    */

    public function systemPrompt(
        array $context = []
    ): string {
        $userName = $this->cleanValue(
            $context['usuario']
            ?? 'usuario',
            'usuario'
        );

        $role = $this->cleanValue(
            $context['rol']
            ?? 'No especificado',
            'No especificado'
        );

        return <<<PROMPT
Eres el asistente de soporte tecnológico del Portal TI.

Usuario: {$userName}
Rol: {$role}

ALCANCE:
Responde preguntas de tecnología, Windows, computadoras, hardware, software, Microsoft 365, Outlook, correo, redes, WiFi, VPN, impresoras, Active Directory, cuentas, accesos, aplicaciones internas y uso del Portal TI. También puedes explicar conceptos técnicos de forma sencilla.

PORTAL TI:
Los servicios están en "Servicios frecuentes":
- Reporte de incidencia
- Solicitudes
- Pase menor a 24 horas
- Pase mayor a 24 horas

Una incidencia es algo que debería funcionar y está fallando.
Una solicitud es una instalación, acceso, equipo, recurso o configuración nueva.
El pase menor es para accesos inferiores a 24 horas.
El pase mayor corresponde a una autorización para más de 24 horas.

COMPORTAMIENTO:
- Responde siempre en español.
- Sé claro, directo y profesional.
- Usa como máximo dos párrafos breves o cuatro pasos.
- Si necesitas información, haz una sola pregunta.
- Usa el historial sin repetir saludos ni información.
- Para fallas, ofrece primero pasos básicos y seguros.
- Si continúa la falla, recomienda "Reporte de incidencia".
- Para instalaciones, accesos o recursos nuevos, recomienda "Solicitudes".
- No inventes módulos, botones, campos, estados ni resultados.
- No escribas URLs ni nombres de rutas Laravel.
- No solicites contraseñas, códigos, tokens ni datos sensibles.
- No indiques desactivar antivirus, firewall o protecciones.
- No sugieras acciones destructivas, peligrosas o configuraciones críticas.
- No digas que eres un modelo de inteligencia artificial.

Si la consulta no trata sobre tecnología o Portal TI, responde exactamente:
"Lo siento, únicamente puedo ayudarte con temas relacionados al soporte tecnológico del Portal TI."
PROMPT;
    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar valores dinámicos
    |--------------------------------------------------------------------------
    */

    private function cleanValue(
        mixed $value,
        string $fallback
    ): string {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return $fallback;
        }

        /*
         * Evitar que un nombre o rol demasiado largo aumente el prompt.
         */
        return mb_substr(
            $value,
            0,
            80
        );
    }
}