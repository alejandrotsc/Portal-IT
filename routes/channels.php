<?php

use App\Models\Usuario;
use Illuminate\Support\Facades\Broadcast;


/*
|--------------------------------------------------------------------------
| Canal privado de notificaciones del usuario
|--------------------------------------------------------------------------
*/

Broadcast::channel(
    'usuarios.{usuarioId}',
    function (
        Usuario $usuario,
        int $usuarioId
    ): bool {
        return (int) $usuario->id
            ===
            (int) $usuarioId;
    }
);