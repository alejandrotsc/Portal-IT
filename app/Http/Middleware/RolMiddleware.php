<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Middleware de control por rol
|--------------------------------------------------------------------------
|
| Verifica que el usuario esté autenticado, tenga una cuenta activa y posea
| uno de los roles permitidos antes de continuar hacia la ruta solicitada.
|
*/

class RolMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Procesar solicitud
    |--------------------------------------------------------------------------
    |
    | Ejecuta las validaciones de autenticación, estado de cuenta y rol antes
    | de permitir que la solicitud continúe hacia el controlador.
    |
    */

    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $usuario = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Validar autenticación
        |--------------------------------------------------------------------------
        |
        | Redirige al login cuando no existe un usuario autenticado en la
        | solicitud actual.
        |
        */

        if (! $usuario) {
            return redirect()
                ->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Validar estado de la cuenta
        |--------------------------------------------------------------------------
        |
        | Si la cuenta fue desactivada, se cierra la sesión, se invalida la
        | sesión actual y se genera un nuevo token CSRF antes de redirigir.
        |
        */

        if (! $usuario->activo) {
            auth()->logout();

            $request
                ->session()
                ->invalidate();

            $request
                ->session()
                ->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'correo' =>
                        'Tu cuenta se encuentra desactivada.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar rol
        |--------------------------------------------------------------------------
        |
        | Comprueba que el rol actual del usuario exista y se encuentre dentro
        | de los roles autorizados recibidos por el middleware.
        |
        */

        $rolActual = $usuario
            ->rol
            ?->nombre;

        if (
            ! $rolActual
            || ! in_array(
                $rolActual,
                $roles,
                true
            )
        ) {
            abort(
                403,
                'No tienes permiso para acceder a este apartado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Continuar solicitud
        |--------------------------------------------------------------------------
        |
        | Si todas las validaciones fueron superadas, la solicitud continúa
        | hacia el siguiente middleware o controlador.
        |
        */

        return $next($request);
    }
}