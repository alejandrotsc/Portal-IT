<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Chatbot\IntentRecognizerInterface;
use App\Services\Chatbot\KeywordIntentRecognizer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IntentRecognizerInterface::class,
            KeywordIntentRecognizer::class
        );
    }

    public function boot(): void
    {
        //
    }
}