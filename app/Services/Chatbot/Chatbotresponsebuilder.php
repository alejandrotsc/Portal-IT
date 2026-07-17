<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Route;

/**
 * Traduce una IntentResult en una respuesta lista para el frontend:
 * texto del bot, botones de acción rápida y, si aplica, un botón de
 * redirección directa al módulo correspondiente del portal.
 */
class ChatbotResponseBuilder
{
    public function build(IntentResult $intent, string $userName): array
    {
        return match ($intent->intent) {
            'incidencia' => $this->forModule(
                key: 'incidencia',
                message: "Entiendo, {$userName}. Parece que necesitas reportar una incidencia técnica. "
                    ."Puedo abrirte el formulario de incidencias para que la registres con el detalle "
                    ."(equipo, descripción del problema y evidencia si tienes)."
            ),

            'solicitud' => $this->forModule(
                key: 'solicitud',
                message: "Perfecto, {$userName}. Esto suena a una solicitud de servicio (equipo, software, "
                    ."cuentas u otro recurso). Te llevo al formulario para crearla."
            ),

            'pase_menor_24h' => $this->forModule(
                key: 'pase_menor_24h',
                message: "Para accesos puntuales de menos de 24 horas se gestiona un pase. "
                    ."Te muestro el formulario para solicitarlo; solo necesitarás indicar el lugar, "
                    ."la fecha y el motivo del acceso."
            ),

            'autorizacion_memorando' => $this->forModule(
                key: 'autorizacion_memorando',
                message: "Para accesos de más de 24 horas se requiere una autorización formal por memorando. "
                    ."Te dirijo al formulario correspondiente; ten a la mano el sustento y el tiempo requerido "
                    ."de acceso."
            ),

            'saludo' => [
                'message' => "¡Hola de nuevo, {$userName}! ¿Qué necesitas hacer hoy?",
                'quick_actions' => $this->defaultQuickActions(),
                'redirect' => null,
                'items' => null,
            ],

            default => [
                'message' => "No estoy seguro de haber entendido bien tu solicitud, {$userName}. "
                    ."¿Podrías elegir una opción o darme un poco más de detalle? Por ejemplo: "
                    ."'mi computador no enciende', 'necesito una licencia de Office', "
                    ."'necesito entrar hoy al centro de datos' o 'quiero saber el estado de mi solicitud'.",
                'quick_actions' => $this->defaultQuickActions(),
                'redirect' => null,
                'items' => null,
            ],
        };
    }

    private function forModule(string $key, string $message): array
    {
        $module = config("chatbot.modules.{$key}");

        $redirect = null;
        if ($module && Route::has($module['create'])) {
            $redirect = [
                'label' => 'Ir a: '.$module['label'],
                'url' => route($module['create']),
            ];
        }

        return [
            'message' => $message,
            'quick_actions' => [
                ['label' => 'Sí, llévame al formulario', 'action' => 'redirect'],
                ['label' => 'Consultar estado de mis gestiones', 'action' => 'send', 'value' => 'consultar estado de mis gestiones'],
                ['label' => 'No era esto, mostrar menú', 'action' => 'send', 'value' => 'menú'],
            ],
            'redirect' => $redirect,
            'items' => null,
        ];
    }

    private function defaultQuickActions(): array
    {
        return [
            ['label' => 'Reportar incidencia', 'action' => 'send', 'value' => 'quiero reportar una incidencia'],
            ['label' => 'Crear solicitud', 'action' => 'send', 'value' => 'necesito crear una solicitud de servicio'],
            ['label' => 'Solicitar pase (< 24h)', 'action' => 'send', 'value' => 'necesito un pase de acceso por menos de 24 horas'],
            ['label' => 'Autorización por memorando (> 24h)', 'action' => 'send', 'value' => 'necesito una autorización por memorando'],
            ['label' => 'Consultar mis gestiones', 'action' => 'send', 'value' => 'consultar estado de mis gestiones'],
        ];
    }
}