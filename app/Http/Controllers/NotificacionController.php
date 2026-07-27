<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listado de notificaciones
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {
        $notificaciones = $request
            ->user()
            ->notifications()
            ->latest('created_at')
            ->paginate(15);

        return view(
            'notificaciones.index',
            compact('notificaciones')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Abrir notificación
    |--------------------------------------------------------------------------
    */

    public function abrir(
        Request $request,
        string $notification
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Buscar únicamente dentro de las notificaciones del usuario
        |--------------------------------------------------------------------------
        */

        $notificacion = $request
            ->user()
            ->notifications()
            ->findOrFail(
                $notification
            );


        /*
        |--------------------------------------------------------------------------
        | Marcar como leída
        |--------------------------------------------------------------------------
        */

        if ($notificacion->unread()) {
            $notificacion->markAsRead();
        }


        /*
        |--------------------------------------------------------------------------
        | Obtener destino
        |--------------------------------------------------------------------------
        */

        $url = $notificacion->data['url']
            ?? route(
                'dashboard',
                absolute: false
            );


        /*
        |--------------------------------------------------------------------------
        | Convertir cualquier URL absoluta a una ruta interna
        |--------------------------------------------------------------------------
        |
        | Ejemplo:
        |
        | http://localhost/administracion/pases/15
        |
        | se convierte en:
        |
        | /administracion/pases/15
        |
        */

        $ruta = parse_url(
            $url,
            PHP_URL_PATH
        );


        /*
        |--------------------------------------------------------------------------
        | Validar ruta
        |--------------------------------------------------------------------------
        */

        if (
            ! is_string($ruta)
            ||
            trim($ruta) === ''
        ) {
            $ruta = route(
                'dashboard',
                absolute: false
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Conservar parámetros GET
        |--------------------------------------------------------------------------
        */

        $query = parse_url(
            $url,
            PHP_URL_QUERY
        );

        if (
            is_string($query)
            &&
            $query !== ''
        ) {
            $ruta .= '?'.$query;
        }


        /*
        |--------------------------------------------------------------------------
        | Redirección interna
        |--------------------------------------------------------------------------
        */

        return redirect()->to(
            $ruta
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar todas como leídas
    |--------------------------------------------------------------------------
    */

    public function marcarTodasComoLeidas(
        Request $request
    ): RedirectResponse {
        $request
            ->user()
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'Todas las notificaciones fueron marcadas como leídas.'
        );
    }
}