<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Controlador de sesiones
    |--------------------------------------------------------------------------
    |
    | Las sesiones se almacenarán en la tabla "sessions".
    |
    */

    'driver' => env(
        'SESSION_DRIVER',
        'database'
    ),


    /*
    |--------------------------------------------------------------------------
    | Duración de la sesión
    |--------------------------------------------------------------------------
    |
    | Cantidad de minutos que la sesión puede permanecer sin actividad.
    | Cada solicitud válida del usuario actualiza su última actividad.
    |
    */

    'lifetime' => (int) env(
        'SESSION_LIFETIME',
        60
    ),


    /*
    |--------------------------------------------------------------------------
    | Finalizar al cerrar el navegador
    |--------------------------------------------------------------------------
    |
    | Cuando está activo, Laravel crea una cookie sin fecha persistente.
    | El navegador debería eliminarla cuando se cierre completamente.
    |
    */

    'expire_on_close' => (bool) env(
        'SESSION_EXPIRE_ON_CLOSE',
        true
    ),


    /*
    |--------------------------------------------------------------------------
    | Cifrado de la sesión
    |--------------------------------------------------------------------------
    |
    | Cifra el contenido de la sesión antes de almacenarlo en la base
    | de datos utilizando la APP_KEY de la aplicación.
    |
    */

    'encrypt' => (bool) env(
        'SESSION_ENCRYPT',
        true
    ),


    /*
    |--------------------------------------------------------------------------
    | Sesiones basadas en archivos
    |--------------------------------------------------------------------------
    */

    'files' => storage_path(
        'framework/sessions'
    ),


    /*
    |--------------------------------------------------------------------------
    | Conexión de base de datos
    |--------------------------------------------------------------------------
    */

    'connection' => env(
        'SESSION_CONNECTION'
    ),


    /*
    |--------------------------------------------------------------------------
    | Tabla de sesiones
    |--------------------------------------------------------------------------
    */

    'table' => env(
        'SESSION_TABLE',
        'sessions'
    ),


    /*
    |--------------------------------------------------------------------------
    | Almacén de caché
    |--------------------------------------------------------------------------
    |
    | Solamente se utiliza con controladores como Redis, DynamoDB,
    | Memcached y otros controladores basados en caché.
    |
    */

    'store' => env(
        'SESSION_STORE'
    ),


    /*
    |--------------------------------------------------------------------------
    | Limpieza de sesiones vencidas
    |--------------------------------------------------------------------------
    |
    | En aproximadamente 2 de cada 100 solicitudes Laravel intentará
    | eliminar registros de sesión que ya hayan vencido.
    |
    */

    'lottery' => [
        2,
        100,
    ],


    /*
    |--------------------------------------------------------------------------
    | Nombre de la cookie
    |--------------------------------------------------------------------------
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(
            (string) env(
                'APP_NAME',
                'laravel'
            )
        ).'-session'
    ),


    /*
    |--------------------------------------------------------------------------
    | Ruta de la cookie
    |--------------------------------------------------------------------------
    */

    'path' => env(
        'SESSION_PATH',
        '/'
    ),


    /*
    |--------------------------------------------------------------------------
    | Dominio de la cookie
    |--------------------------------------------------------------------------
    |
    | En local puede permanecer como null.
    |
    */

    'domain' => env(
        'SESSION_DOMAIN'
    ),


    /*
    |--------------------------------------------------------------------------
    | Cookie exclusiva para HTTPS
    |--------------------------------------------------------------------------
    |
    | En desarrollo local con HTTP debe ser false.
    | En producción con HTTPS debe ser true.
    |
    */

    'secure' => env(
        'SESSION_SECURE_COOKIE',
        false
    ),


    /*
    |--------------------------------------------------------------------------
    | Cookie inaccesible desde JavaScript
    |--------------------------------------------------------------------------
    |
    | Reduce el riesgo de que scripts del navegador lean directamente
    | el identificador de sesión.
    |
    */

    'http_only' => (bool) env(
        'SESSION_HTTP_ONLY',
        true
    ),


    /*
    |--------------------------------------------------------------------------
    | SameSite
    |--------------------------------------------------------------------------
    |
    | "lax" protege contra solicitudes entre sitios y permite que el
    | usuario abra el enlace mágico desde su aplicación de correo.
    |
    */

    'same_site' => env(
        'SESSION_SAME_SITE',
        'lax'
    ),


    /*
    |--------------------------------------------------------------------------
    | Cookies particionadas
    |--------------------------------------------------------------------------
    */

    'partitioned' => (bool) env(
        'SESSION_PARTITIONED_COOKIE',
        false
    ),


    /*
    |--------------------------------------------------------------------------
    | Serialización
    |--------------------------------------------------------------------------
    |
    | JSON evita almacenar objetos PHP serializados dentro de la sesión.
    |
    */

    'serialization' => 'json',

];