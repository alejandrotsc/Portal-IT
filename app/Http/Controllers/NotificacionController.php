<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
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


    public function abrir(
        Request $request,
        string $notification
    ): RedirectResponse {
        $notificacion = $request
            ->user()
            ->notifications()
            ->findOrFail(
                $notification
            );

        if ($notificacion->unread()) {
            $notificacion->markAsRead();
        }

        $url = $notificacion->data['url']
            ?? route('dashboard');

        return redirect()->to(
            $url
        );
    }


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
