<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailDeliveryController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\MemorandoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Autenticación pública sin contraseña
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Inicio de sesión mediante enlace mágico
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/login',
        [
            AuthController::class,
            'login',
        ]
    )->name('login');


    Route::post(
        '/login',
        [
            AuthController::class,
            'authenticate',
        ]
    )
        ->middleware('throttle:5,1')
        ->name('login.authenticate');


    Route::get(
        '/acceso/{token}',
        [
            AuthController::class,
            'magicLogin',
        ]
    )
        ->where(
            'token',
            '[a-f0-9]{64}'
        )
        ->middleware('throttle:10,1')
        ->name('login.magic');


    /*
    |--------------------------------------------------------------------------
    | Registro
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/registro',
        [
            AuthController::class,
            'register',
        ]
    )->name('register');


    Route::post(
        '/registro',
        [
            AuthController::class,
            'store',
        ]
    )
        ->middleware('throttle:5,1')
        ->name('register.store');


    /*
    |--------------------------------------------------------------------------
    | Verificación del correo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/registro/verificar',
        [
            AuthController::class,
            'verification',
        ]
    )->name('register.verification');


    Route::post(
        '/registro/verificar',
        [
            AuthController::class,
            'verify',
        ]
    )
        ->middleware(
            'throttle:verificar-codigo'
        )
        ->name('register.verify');


    Route::post(
        '/registro/reenviar-codigo',
        [
            AuthController::class,
            'resendCode',
        ]
    )
        ->middleware(
            'throttle:reenviar-codigo'
        )
        ->name('register.resend');

});


/*
|--------------------------------------------------------------------------
| Rutas para usuarios autenticados
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/',
        [
            DashboardController::class,
            'index',
        ]
    )->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Cerrar sesión
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [
            AuthController::class,
            'logout',
        ]
    )->name('logout');


    /*
    |--------------------------------------------------------------------------
    | Notificaciones
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notificaciones',
        [NotificacionController::class, 'index']
    )->name('notificaciones.index');


    Route::get(
        '/notificaciones/{notification}',
        [NotificacionController::class, 'abrir']
    )->name('notificaciones.abrir');


    Route::patch(
        '/notificaciones/marcar-todas/leidas',
        [NotificacionController::class, 'marcarTodasComoLeidas']
    )->name('notificaciones.marcar-todas');


    /*
    |--------------------------------------------------------------------------
    | Estado de correos en cola
    |--------------------------------------------------------------------------
    |
    | Permite que el frontend consulte si un correo continúa pendiente,
    | fue enviado correctamente o terminó fallando.
    |
    */

    Route::get(
        '/email-deliveries/{emailDelivery}/status',
        [
            EmailDeliveryController::class,
            'status',
        ]
    )
        ->whereNumber('emailDelivery')
        ->name('email-deliveries.status');


    /*
    |--------------------------------------------------------------------------
    | Memorandos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos',
        [
            MemorandoController::class,
            'create',
        ]
    )->name('memorandos.create');


    Route::post(
        '/memorandos',
        [
            MemorandoController::class,
            'store',
        ]
    )->name('memorandos.store');


    /*
    |--------------------------------------------------------------------------
    | Pase menor a 24 horas
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/pase-temporal',
        [
            MemorandoController::class,
            'createPaseTemporal',
        ]
    )->name('memorandos.pase_temporal');


    Route::post(
        '/memorandos/pase-temporal',
        [
            MemorandoController::class,
            'storePaseTemporal',
        ]
    )->name('memorandos.pase_temporal.store');


    /*
    |--------------------------------------------------------------------------
    | Pase mayor a 24 horas
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/autorizacion',
        [
            MemorandoController::class,
            'createAutorizacion',
        ]
    )->name('memorandos.autorizacion');


    /*
    |--------------------------------------------------------------------------
    | Historial centralizado de pases
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/mis-pases',
        [
            MemorandoController::class,
            'misPases',
        ]
    )->name('memorandos.mis-pases');


    Route::get(
        '/mis-pases/{memorando}',
        [
            MemorandoController::class,
            'showPase',
        ]
    )->name('memorandos.show-pase');


    /*
    |--------------------------------------------------------------------------
    | Solicitud de compra
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/compra',
        [
            MemorandoController::class,
            'createCompra',
        ]
    )->name('memorandos.create.compra');


    /*
    |--------------------------------------------------------------------------
    | Vista previa dinámica
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/preview/{tipo}',
        [
            MemorandoController::class,
            'previewDinamico',
        ]
    )->name('memorandos.preview');


    /*
    |--------------------------------------------------------------------------
    | Histórico general
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/historico',
        [
            MemorandoController::class,
            'historico',
        ]
    )->name('memorandos.historico');

    /*
    |--------------------------------------------------------------------------
    | Visualizar PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/{memorando}/pdf',
        [
            MemorandoController::class,
            'pdf',
        ]
    )->name('memorandos.pdf');


    /*
    |--------------------------------------------------------------------------
    | Descargar PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/{memorando}/download',
        [
            MemorandoController::class,
            'download',
        ]
    )->name('memorandos.download');



    /*
    |--------------------------------------------------------------------------
    | Solicitudes del usuario
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/mis-solicitudes',
        [
            SolicitudController::class,
            'misSolicitudes',
        ]
    )->name('mis-solicitudes');


    Route::get(
        '/solicitudes/create',
        [
            SolicitudController::class,
            'create',
        ]
    )->name('solicitudes.create');


    Route::post(
        '/solicitudes',
        [
            SolicitudController::class,
            'store',
        ]
    )->name('solicitudes.store');


    /*
    | Esta ruta debe permanecer después de:
    |
    | /solicitudes/create
    */

    Route::get(
        '/solicitudes/{solicitud}',
        [
            SolicitudController::class,
            'show',
        ]
    )->name('solicitudes.show');


    /*
    |--------------------------------------------------------------------------
    | Avisos vigentes del Portal TI
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/avisos-ti',
        [
            AvisoController::class,
            'publicos',
        ]
    )->name('avisos.publicos');


    /*
    |--------------------------------------------------------------------------
    | Chatbot Asistente TI
    |--------------------------------------------------------------------------
    */

    Route::prefix('chatbot')
        ->name('chatbot.')
        ->group(function () {

            Route::post(
                '/warm-up',
                [
                    ChatbotController::class,
                    'warmUp',
                ]
            )->name('warm-up');


            Route::post(
                '/message',
                [
                    ChatbotController::class,
                    'message',
                ]
            )->name('message');


            Route::post(
                '/stream',
                [
                    ChatbotController::class,
                    'stream',
                ]
            )->name('stream');


            Route::get(
                '/estado',
                [
                    ChatbotController::class,
                    'estado',
                ]
            )->name('estado');


            Route::post(
                '/feedback',
                [
                    ChatbotController::class,
                    'feedback',
                ]
            )->name('feedback');

        });


    /*
    |--------------------------------------------------------------------------
    | Incidencias
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'incidencias',
        IncidenciaController::class
    )->only([
        'create',
        'store',
        'show',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Incidencias del usuario autenticado
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/mis-incidencias',
        [
            IncidenciaController::class,
            'misIncidencias',
        ]
    )->name('mis-incidencias');


});


/*
|--------------------------------------------------------------------------
| Gestión interna de solicitudes
|--------------------------------------------------------------------------
|
| Acceso:
|
| - UsuarioTI
| - Administrador
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'rol:UsuarioTI,Administrador',
])
    ->prefix('administracion')
    ->name('admin.')
    ->group(function () {

        /*
|--------------------------------------------------------------------------
| Administración de pases
|--------------------------------------------------------------------------
*/

Route::get(
    '/pases',
    [
        MemorandoController::class,
        'administracionPases',
    ]
)->name('pases');


/*
|--------------------------------------------------------------------------
| Detalle del pase
|--------------------------------------------------------------------------
*/

Route::get(
    '/pases/{memorando}',
    [
        MemorandoController::class,
        'showAdministracionPase',
    ]
)->name('pases.show');


/*
|--------------------------------------------------------------------------
| Aprobar pase
|--------------------------------------------------------------------------
*/

Route::patch(
    '/pases/{memorando}/aprobar',
    [
        MemorandoController::class,
        'aprobarPase',
    ]
)->name('pases.aprobar');


/*
|--------------------------------------------------------------------------
| Rechazar pase
|--------------------------------------------------------------------------
*/

Route::patch(
    '/pases/{memorando}/rechazar',
    [
        MemorandoController::class,
        'rechazarPase',
    ]
)->name('pases.rechazar');


        /*
        |--------------------------------------------------------------------------
        | Administración de solicitudes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/solicitudes',
            [
                SolicitudController::class,
                'administracion',
            ]
        )->name('solicitudes');


        /*
        |--------------------------------------------------------------------------
        | Detalle
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/solicitudes/{solicitud}',
            [
                SolicitudController::class,
                'showAdministracion',
            ]
        )->name('solicitudes.show');


        /*
        |--------------------------------------------------------------------------
        | Finalizar
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/solicitudes/{solicitud}/finalizar',
            [
                SolicitudController::class,
                'finalizar',
            ]
        )->name('solicitudes.finalizar');


        /*
        |--------------------------------------------------------------------------
        | Cancelar
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/solicitudes/{solicitud}/cancelar',
            [
                SolicitudController::class,
                'cancelar',
            ]
        )->name('solicitudes.cancelar');

    });


/*
|--------------------------------------------------------------------------
| Administración
|--------------------------------------------------------------------------
|
| Acceso exclusivo:
|
| - Administrador
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'rol:Administrador',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'usuarios',
        UsuarioController::class
    )->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Activar o desactivar usuario
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/usuarios/{usuario}/estado',
        [
            UsuarioController::class,
            'changeStatus',
        ]
    )->name('usuarios.change-status');


    /*
    |--------------------------------------------------------------------------
    | Reenviar verificación
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/usuarios/{usuario}/reenviar-verificacion',
        [
            UsuarioController::class,
            'resendVerification',
        ]
    )
        ->middleware('throttle:3,10')
        ->name('usuarios.resend-verification');


    /*
    |--------------------------------------------------------------------------
    | Avisos de TI
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'avisos',
        AvisoController::class
    )->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Activar o desactivar aviso
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/avisos/{aviso}/estado',
        [
            AvisoController::class,
            'changeStatus',
        ]
    )->name('avisos.change-status');


    /*
    |--------------------------------------------------------------------------
    | Administración de incidencias
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/administracion/incidencias',
        [
            IncidenciaController::class,
            'administracion',
        ]
    )->name('admin.incidencias');


    /*
    |--------------------------------------------------------------------------
    | Detalle administrativo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/administracion/incidencias/{incidencia}',
        [
            IncidenciaController::class,
            'showAdministracion',
        ]
    )->name('admin.incidencias.show');


    /*
    |--------------------------------------------------------------------------
    | Iniciar atención
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/administracion/incidencias/{incidencia}/iniciar',
        [
            IncidenciaController::class,
            'iniciar',
        ]
    )->name('admin.incidencias.iniciar');


    /*
    |--------------------------------------------------------------------------
    | Resolver
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/administracion/incidencias/{incidencia}/resolver',
        [
            IncidenciaController::class,
            'resolver',
        ]
    )->name('admin.incidencias.resolver');


    /*
    |--------------------------------------------------------------------------
    | Reabrir
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/administracion/incidencias/{incidencia}/reabrir',
        [
            IncidenciaController::class,
            'reabrir',
        ]
    )->name('admin.incidencias.reabrir');


    /*
    |--------------------------------------------------------------------------
    | Actualizar prioridad
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/administracion/incidencias/{incidencia}/prioridad',
        [
            IncidenciaController::class,
            'actualizarPrioridad',
        ]
    )->name('admin.incidencias.prioridad');

});