<?php

namespace App\Providers;

use App\Models\Aviso;
use App\Services\Chatbot\IntentRecognizerInterface;
use App\Services\Chatbot\KeywordIntentRecognizer;
use App\View\Composers\SupportWidgetComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Registrar servicios
    |--------------------------------------------------------------------------
    |
    | Registra las dependencias de la aplicación y vincula la interfaz
    | utilizada por el chatbot con su implementación correspondiente.
    |
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
    |
    | Configura los limitadores de solicitudes y registra los elementos
    | compartidos que estarán disponibles dentro de las vistas.
    |
    */

    public function boot(): void
    {
        $this->configurarLimiteVerificacion();

        $this->configurarLimiteReenvio();

        $this->compartirAvisosTicker();

        $this->registrarWidgetSoporte();
    }

    /*
    |--------------------------------------------------------------------------
    | Compartir avisos visibles
    |--------------------------------------------------------------------------
    |
    | Los avisos se comparten con el layout principal y los dashboards.
    | La colección se reutiliza durante la misma solicitud para evitar
    | ejecutar la consulta más de una vez.
    |
    */

    private function compartirAvisosTicker(): void
    {
        $avisosTicker = null;

        View::composer(
            [
                'layouts.app',
                'dashboard.*',
            ],
            function ($view) use (
                &$avisosTicker
            ): void {
                if ($avisosTicker === null) {
                    $ahora = now();

                    $avisosTicker = Aviso::query()
                        ->where(
                            'activo',
                            true
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Publicación inmediata o fecha ya alcanzada
                        |--------------------------------------------------------------------------
                        |
                        | Incluye los avisos sin fecha de inicio definida o cuya
                        | fecha de publicación ya fue alcanzada.
                        |
                        */

                        ->where(
                            function ($query) use ($ahora) {
                                $query
                                    ->whereNull(
                                        'fecha_inicio'
                                    )
                                    ->orWhere(
                                        'fecha_inicio',
                                        '<=',
                                        $ahora
                                    );
                            }
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Sin vencimiento o fecha todavía vigente
                        |--------------------------------------------------------------------------
                        |
                        | Incluye los avisos sin fecha de finalización o aquellos
                        | cuya vigencia todavía no ha terminado.
                        |
                        */

                        ->where(
                            function ($query) use ($ahora) {
                                $query
                                    ->whereNull(
                                        'fecha_fin'
                                    )
                                    ->orWhere(
                                        'fecha_fin',
                                        '>=',
                                        $ahora
                                    );
                            }
                        )
                        ->orderByDesc(
                            'created_at'
                        )
                        ->limit(10)
                        ->get([
                            'id',
                            'titulo',
                            'mensaje',
                            'fecha_inicio',
                            'fecha_fin',
                            'created_at',
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Compartir colección con la vista
                |--------------------------------------------------------------------------
                */

                $view->with(
                    'avisosTicker',
                    $avisosTicker
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar widget de soporte
    |--------------------------------------------------------------------------
    |
    | Se registran nombres de vistas Blade, no nombres de rutas.
    | La obtención de las guardias se encuentra centralizada dentro de
    | SupportWidgetComposer.
    |
    */

    private function registrarWidgetSoporte(): void
    {
        View::composer(
            'partials.support-widget',
            SupportWidgetComposer::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Límite para verificar código
    |--------------------------------------------------------------------------
    |
    | Protege el formulario donde el usuario introduce el código de
    | verificación limitando la cantidad de intentos permitidos.
    |
    */

    private function configurarLimiteVerificacion(): void
    {
        RateLimiter::for(
            'verificar-codigo',
            function (
                Request $request
            ) {
                /*
                |--------------------------------------------------------------------------
                | Construir identificador
                |--------------------------------------------------------------------------
                */

                $identificador =
                    $this->identificador(
                        $request
                    );

                /*
                |--------------------------------------------------------------------------
                | Aplicar límite de intentos
                |--------------------------------------------------------------------------
                |
                | Permite hasta cinco intentos por minuto para cada combinación
                | de correo y dirección IP.
                |
                */

                return Limit::perMinute(5)
                    ->by(
                        'verificar-codigo:'
                        .$identificador
                    )
                    ->response(
                        function (
                            Request $request,
                            array $headers
                        ) {
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
    | Este límite es independiente del límite de validación de códigos
    | y controla la frecuencia con la que puede solicitarse uno nuevo.
    |
    */

    private function configurarLimiteReenvio(): void
    {
        RateLimiter::for(
            'reenviar-codigo',
            function (
                Request $request
            ) {
                /*
                |--------------------------------------------------------------------------
                | Construir identificador
                |--------------------------------------------------------------------------
                */

                $identificador =
                    $this->identificador(
                        $request
                    );

                /*
                |--------------------------------------------------------------------------
                | Aplicar límite de reenvíos
                |--------------------------------------------------------------------------
                |
                | Permite solicitar hasta dos códigos por minuto para cada
                | combinación de correo y dirección IP.
                |
                */

                return Limit::perMinute(2)
                    ->by(
                        'reenviar-codigo:'
                        .$identificador
                    )
                    ->response(
                        function (
                            Request $request,
                            array $headers
                        ) {
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
    | Combina el correo y la dirección IP. El correo se procesa con
    | SHA-256 para evitar que quede visible dentro de las claves de caché.
    |
    */

    private function identificador(
        Request $request
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Obtener correo normalizado
        |--------------------------------------------------------------------------
        |
        | Recupera el correo desde la solicitud o desde la sesión y lo
        | normaliza antes de construir el identificador.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Generar identificador seguro
        |--------------------------------------------------------------------------
        */

        return hash(
            'sha256',
            $correo
            .'|'
            .$request->ip()
        );
    }
}