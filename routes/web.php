<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\MemorandoController;
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
        [AuthController::class, 'login']
    )->name('login');


    Route::post(
        '/login',
        [AuthController::class, 'authenticate']
    )
        ->middleware('throttle:5,1')
        ->name('login.authenticate');


    Route::get(
        '/acceso/{token}',
        [AuthController::class, 'magicLogin']
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
        [AuthController::class, 'register']
    )->name('register');


    Route::post(
        '/registro',
        [AuthController::class, 'store']
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
        [AuthController::class, 'verification']
    )->name('register.verification');


    /*
    | Utiliza el limitador "verificar-codigo" configurado
    | en AppServiceProvider.
    */

    Route::post(
        '/registro/verificar',
        [AuthController::class, 'verify']
    )
        ->middleware(
            'throttle:verificar-codigo'
        )
        ->name('register.verify');


    /*
    | Utiliza un contador independiente.
    |
    | Los intentos fallidos de verificación no bloquean
    | el primer reenvío del código.
    */

    Route::post(
        '/registro/reenviar-codigo',
        [AuthController::class, 'resendCode']
    )
        ->middleware(
            'throttle:reenviar-codigo'
        )
        ->name('register.resend');
});


/*
|--------------------------------------------------------------------------
| Rutas autenticadas
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
        [DashboardController::class, 'index']
    )->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Cerrar sesión
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )->name('logout');


    /*
    |--------------------------------------------------------------------------
    | Memorandos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos',
        [MemorandoController::class, 'create']
    )->name('memorandos.create');


    Route::post(
        '/memorandos',
        [MemorandoController::class, 'store']
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
        [MemorandoController::class, 'misPases']
    )->name('memorandos.mis-pases');


    Route::get(
        '/mis-pases/{memorando}',
        [MemorandoController::class, 'showPase']
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
        [MemorandoController::class, 'historico']
    )->name('memorandos.historico');


    /*
    |--------------------------------------------------------------------------
    | Descargar PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/{id}/download',
        [MemorandoController::class, 'download']
    )->name('memorandos.download');


    /*
    |--------------------------------------------------------------------------
    | Visualizar PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memorandos/{id}/pdf',
        [MemorandoController::class, 'pdf']
    )->name('memorandos.pdf');


    /*
    |--------------------------------------------------------------------------
    | Solicitudes
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
        [SolicitudController::class, 'create']
    )->name('solicitudes.create');


    Route::post(
        '/solicitudes',
        [SolicitudController::class, 'store']
    )->name('solicitudes.store');


    /*
    | Esta ruta debe permanecer después de:
    |
    | /solicitudes/create
    */

    Route::get(
        '/solicitudes/{solicitud}',
        [SolicitudController::class, 'show']
    )->name('solicitudes.show');


    /*
    |--------------------------------------------------------------------------
    | Chatbot Asistente TI
    |--------------------------------------------------------------------------
    */

    Route::prefix('chatbot')
        ->name('chatbot.')
        ->group(function () {

            /*
            | Preparar el chatbot.
            */

            Route::post(
                '/warm-up',
                [ChatbotController::class, 'warmUp']
            )->name('warm-up');


            /*
            | Respuesta JSON completa.
            */

            Route::post(
                '/message',
                [ChatbotController::class, 'message']
            )->name('message');


            /*
            | Respuesta progresiva.
            */

            Route::post(
                '/stream',
                [ChatbotController::class, 'stream']
            )->name('stream');


            /*
            | Consultar estado de gestiones.
            */

            Route::get(
                '/estado',
                [ChatbotController::class, 'estado']
            )->name('estado');


            /*
            | Registrar utilidad de la respuesta.
            */

            Route::post(
                '/feedback',
                [ChatbotController::class, 'feedback']
            )->name('feedback');
        });


    /*
    |--------------------------------------------------------------------------
    | Incidencias / Tickets
    |--------------------------------------------------------------------------
    |
    | Usuarios:
    | - Crear tickets.
    | - Consultar seguimiento.
    |
    | UsuarioTI:
    | - Ver cola.
    | - Tomar casos.
    | - Resolver.
    |
    | Administrador:
    | - Asignación manual.
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'incidencias',
        IncidenciaController::class
    )->only([
        'index',
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


    /*
    |--------------------------------------------------------------------------
    | Acciones de soporte
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/incidencias/{incidencia}/tomar',
        [IncidenciaController::class, 'tomar']
    )->name('incidencias.tomar');


    Route::post(
        '/incidencias/{incidencia}/asignar',
        [IncidenciaController::class, 'asignar']
    )->name('incidencias.asignar');


    Route::post(
        '/incidencias/{incidencia}/diagnostico',
        [
            IncidenciaController::class,
            'diagnostico',
        ]
    )->name('incidencias.diagnostico');


    Route::post(
        '/incidencias/{incidencia}/resolver',
        [IncidenciaController::class, 'resolver']
    )->name('incidencias.resolver');


    Route::post(
        '/incidencias/{incidencia}/cerrar',
        [IncidenciaController::class, 'cerrar']
    )->name('incidencias.cerrar');
});


/*
|--------------------------------------------------------------------------
| Administración
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
    );


    Route::patch(
        '/usuarios/{usuario}/estado',
        [
            UsuarioController::class,
            'changeStatus',
        ]
    )->name('usuarios.change-status');


    Route::post(
        '/usuarios/{usuario}/reenviar-verificacion',
        [
            UsuarioController::class,
            'resendVerification',
        ]
    )->name('usuarios.resend-verification');


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


    Route::post(
        '/administracion/incidencias/{incidencia}/asignar',
        [
            IncidenciaController::class,
            'asignar',
        ]
    )->name('admin.incidencias.asignar');
});