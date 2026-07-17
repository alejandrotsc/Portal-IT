<?php

namespace App\Providers;

use App\Services\Chatbot\IntentRecognizerInterface;
use App\Services\Chatbot\KeywordIntentRecognizer;
use Illuminate\Support\ServiceProvider;

/**
 * Registra el motor de reconocimiento de intención actual (por palabras clave).
 *
 * Para la futura integración con IA, el único cambio necesario en todo
 * el chatbot es esta línea de binding:
 *
 *   $this->app->bind(IntentRecognizerInterface::class, AiIntentRecognizer::class);
 *
 * Recuerda registrar este provider en bootstrap/providers.php (Laravel 11+)
 * o en config/app.php dentro de 'providers' (Laravel 10 e inferiores).
 */
class ChatbotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IntentRecognizerInterface::class, KeywordIntentRecognizer::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/chatbot.php', 'chatbot');
    }
}