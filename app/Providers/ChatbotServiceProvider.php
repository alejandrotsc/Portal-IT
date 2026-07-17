<?php

namespace App\Providers;

use App\Services\Chatbot\AI\AIServiceInterface;
use App\Services\Chatbot\AI\OllamaAIService;
use App\Services\Chatbot\IntentRecognizerInterface;
use App\Services\Chatbot\KeywordIntentRecognizer;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            config_path('chatbot.php'),
            'chatbot'
        );

        $this->mergeConfigFrom(
            config_path('chatbot_diagnostics.php'),
            'chatbot_diagnostics'
        );

        $this->app->singleton(
            IntentRecognizerInterface::class,
            KeywordIntentRecognizer::class
        );

        $this->app->singleton(
            AIServiceInterface::class,
            OllamaAIService::class
        );
    }

    public function boot(): void
    {
        //
    }
}