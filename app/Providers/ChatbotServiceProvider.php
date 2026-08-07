<?php

namespace App\Providers;

use App\Services\Chatbot\AI\AIServiceInterface;
use App\Services\Chatbot\AI\OllamaAIService;
use App\Services\Chatbot\IntentRecognizerInterface;
use App\Services\Chatbot\KeywordIntentRecognizer;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Registrar servicios
    |--------------------------------------------------------------------------
    |
    | Registra la configuración y las dependencias utilizadas por el
    | chatbot para el reconocimiento de intenciones y procesamiento
    | de consultas mediante inteligencia artificial.
    |
    */

    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Configuración principal del chatbot
        |--------------------------------------------------------------------------
        |
        | Integra la configuración general del chatbot con la configuración
        | disponible dentro de la aplicación.
        |
        */

        $this->mergeConfigFrom(
            config_path('chatbot.php'),
            'chatbot'
        );

        /*
        |--------------------------------------------------------------------------
        | Configuración de diagnósticos
        |--------------------------------------------------------------------------
        |
        | Integra la configuración utilizada por los mecanismos de
        | diagnóstico y supervisión del chatbot.
        |
        */

        $this->mergeConfigFrom(
            config_path('chatbot_diagnostics.php'),
            'chatbot_diagnostics'
        );

        /*
        |--------------------------------------------------------------------------
        | Reconocedor de intenciones
        |--------------------------------------------------------------------------
        |
        | Registra una única instancia del reconocedor basado en palabras
        | clave para resolver la interfaz de reconocimiento de intenciones.
        |
        */

        $this->app->singleton(
            IntentRecognizerInterface::class,
            KeywordIntentRecognizer::class
        );

        /*
        |--------------------------------------------------------------------------
        | Servicio de inteligencia artificial
        |--------------------------------------------------------------------------
        |
        | Registra Ollama como implementación del servicio utilizado por
        | el chatbot para procesar consultas mediante inteligencia artificial.
        |
        */

        $this->app->singleton(
            AIServiceInterface::class,
            OllamaAIService::class
        );

        /*
        |--------------------------------------------------------------------------
        | Conocimiento del portal
        |--------------------------------------------------------------------------
        |
        | Registra una instancia compartida del servicio encargado de
        | proporcionar conocimiento específico del Portal TI al chatbot.
        |
        */

        $this->app->singleton(
            PortalKnowledge::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Inicializar servicios
    |--------------------------------------------------------------------------
    |
    | Punto de inicialización del proveedor. Actualmente no requiere
    | ejecutar configuraciones adicionales durante el arranque.
    |
    */

    public function boot(): void
    {
        //
    }
}