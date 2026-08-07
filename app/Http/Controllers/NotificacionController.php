<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| Controlador de notificaciones
|--------------------------------------------------------------------------
|
| Gestiona el listado de notificaciones del usuario, la apertura segura de
| cada elemento, el marcado como leído y la redirección hacia destinos internos
| del Portal TI.
|
*/

class NotificacionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listado de notificaciones
    |--------------------------------------------------------------------------
    |
    | Recupera las notificaciones del usuario autenticado, ordenadas desde la
    | más reciente y paginadas para su visualización.
    |
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
    |
    | Busca la notificación dentro del usuario autenticado, la marca como leída
    | cuando corresponde y redirige de forma segura a su destino interno.
    |
    */

    public function abrir(
        Request $request,
        string $notification
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Buscar únicamente dentro de las notificaciones del usuario
        |--------------------------------------------------------------------------
        |
        | Evita consultar notificaciones pertenecientes a otros usuarios.
        |
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
        |
        | Actualiza el estado solamente cuando la notificación aún se encuentra
        | pendiente de lectura.
        |
        */

        if ($notificacion->unread()) {
            $notificacion->markAsRead();
        }

        /*
        |--------------------------------------------------------------------------
        | Obtener destino
        |--------------------------------------------------------------------------
        |
        | Utiliza la URL almacenada en los datos de la notificación o, como
        | respaldo, la ruta interna del dashboard.
        |
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
        |
        | Si no se obtiene una ruta utilizable, se utiliza el dashboard como
        | destino seguro predeterminado.
        |
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
        |
        | Recupera y adjunta nuevamente la cadena de consulta de la URL original
        | para no perder filtros o parámetros necesarios.
        |
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
        |
        | Envía al usuario hacia la ruta normalizada asociada con la
        | notificación seleccionada.
        |
        */

        return redirect()->to(
            $ruta
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Marcar todas como leídas
    |--------------------------------------------------------------------------
    |
    | Actualiza en una sola operación todas las notificaciones pendientes del
    | usuario autenticado y regresa a la vista anterior.
    |
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
