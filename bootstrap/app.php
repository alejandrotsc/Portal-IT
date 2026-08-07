<?php

use App\Http\Middleware\RolMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Configuración principal de la aplicación
|--------------------------------------------------------------------------
|
| Inicializa la aplicación Laravel utilizando como directorio base la raíz
| del proyecto y registra posteriormente las rutas, middleware y reglas
| generales para el manejo de excepciones.
|
*/

return Application::configure(
    basePath: dirname(__DIR__)
)

    /*
    |--------------------------------------------------------------------------
    | Configuración de rutas
    |--------------------------------------------------------------------------
    |
    | Define los archivos utilizados para las rutas web, comandos de consola
    | y canales de broadcasting, además del endpoint de comprobación de salud.
    |
    */

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | Configuración de middleware
    |--------------------------------------------------------------------------
    |
    | Registra los alias personalizados utilizados por las rutas de la
    | aplicación. El alias "rol" permite aplicar RolMiddleware de forma
    | declarativa sobre los grupos o rutas que requieran control por rol.
    |
    */

    ->withMiddleware(
        function (Middleware $middleware): void {
            $middleware->alias([
                'rol' => RolMiddleware::class,
            ]);
        }
    )

    /*
    |--------------------------------------------------------------------------
    | Configuración de excepciones
    |--------------------------------------------------------------------------
    |
    | Indica que las solicitudes dirigidas a rutas bajo el prefijo "api/"
    | deben recibir las excepciones en formato JSON en lugar de respuestas
    | HTML convencionales.
    |
    */

    ->withExceptions(
        function (Exceptions $exceptions): void {
            $exceptions->shouldRenderJsonWhen(
                fn (Request $request) =>
                    $request->is('api/*'),
            );
        }
    )

    /*
    |--------------------------------------------------------------------------
    | Crear aplicación
    |--------------------------------------------------------------------------
    |
    | Finaliza la configuración y construye la instancia principal de
    | Laravel que será utilizada durante el ciclo de vida de la aplicación.
    |
    */

    ->create();