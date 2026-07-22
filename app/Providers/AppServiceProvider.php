<?php

namespace App\Providers;

use App\Services\Chatbot\IntentRecognizerInterface;
use App\Services\Chatbot\KeywordIntentRecognizer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Registrar servicios
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        $this->app->bind(
            IntentRecognizerInterface::class,
            KeywordIntentRecognizer::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Inicializar servicios
    |--------------------------------------------------------------------------
    */

    public function boot(): void
    {
        $this->configurarLimiteVerificacion();

        $this->configurarLimiteReenvio();
    }

    /*
    |--------------------------------------------------------------------------
    | Límite para verificar código
    |--------------------------------------------------------------------------
    |
    | Este límite protege el formulario donde se introduce el código
    | de seis dígitos.
    |
    | Es independiente del límite para solicitar un código nuevo.
    |--------------------------------------------------------------------------
    */

    private function configurarLimiteVerificacion(): void
    {
        RateLimiter::for(
            'verificar-codigo',
            function (Request $request) {
                $identificador = $this->identificador(
                    $request
                );

                return Limit::perMinute(5)
                    ->by(
                        'verificar-codigo:'
                        . $identificador
                    )
                    ->response(
                        function (
                            Request $request,
                            array $headers
                        ) {
                            /*
                            | En lugar de mostrar una pantalla técnica 429,
                            | regresar al formulario con un mensaje entendible.
                            */

                            return redirect()
                                ->back()
                                ->withInput(
                                    $request->except(
                                        'codigo'
                                    )
                                )
                                ->withErrors([
                                    'codigo' =>
                                        'Realizaste demasiados intentos. El código fue bloqueado; solicita uno nuevo.',
                                ]);
                        }
                    );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Límite para reenviar código
    |--------------------------------------------------------------------------
    |
    | Este contador es completamente diferente al de verificación.
    | Cinco códigos incorrectos no bloquean el primer reenvío.
    |--------------------------------------------------------------------------
    */

    private function configurarLimiteReenvio(): void
    {
        RateLimiter::for(
            'reenviar-codigo',
            function (Request $request) {
                $identificador = $this->identificador(
                    $request
                );

                return Limit::perMinute(2)
                    ->by(
                        'reenviar-codigo:'
                        . $identificador
                    )
                    ->response(
                        function (
                            Request $request,
                            array $headers
                        ) {
                            /*
                            | Evitar mostrar directamente:
                            | 429 Too Many Requests
                            */

                            return redirect()
                                ->back()
                                ->withInput(
                                    $request->except(
                                        'codigo'
                                    )
                                )
                                ->withErrors([
                                    'codigo' =>
                                        'Solicitaste varios códigos recientemente. Espera un minuto antes de solicitar otro.',
                                ]);
                        }
                    );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Construir identificador del limitador
    |--------------------------------------------------------------------------
    |
    | Combina el correo y la dirección IP.
    |
    | El correo se procesa con SHA-256 para no dejarlo directamente
    | visible dentro de las claves almacenadas en caché.
    |--------------------------------------------------------------------------
    */

    private function identificador(
        Request $request
    ): string {
        $correo = mb_strtolower(
            trim(
                (string) (
                    $request->input(
                        'correo'
                    )
                    ?? $request
                        ->session()
                        ->get(
                            'correo_verificacion',
                            ''
                        )
                )
            )
        );

        return hash(
            'sha256',
            $correo
            . '|'
            . $request->ip()
        );
    }
}