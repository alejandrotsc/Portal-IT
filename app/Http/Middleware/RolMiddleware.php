<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
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
        */

        if (! $usuario) {
            return redirect()
                ->route('login');
        }


        /*
        |--------------------------------------------------------------------------
        | Validar estado de la cuenta
        |--------------------------------------------------------------------------
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


        return $next($request);
    }
}