<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MemorandoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\ChatbotController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');

Route::get('/registro', [AuthController::class, 'register'])->name('register');
Route::post('/registro', [AuthController::class, 'store'])->name('register.store');


/*
|--------------------------------------------------------------------------
| Recuperación contraseña
|--------------------------------------------------------------------------
*/

Route::get('/recuperar-contrasena', [AuthController::class, 'forgotPassword'])
    ->name('password.request');

Route::post('/recuperar-contrasena', [AuthController::class, 'sendResetLink'])
    ->name('password.email');


/*
|--------------------------------------------------------------------------
| Rutas autenticadas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {


    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');


    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    /*
    |--------------------------------------------------------------------------
    | MEMORANDOS
    |--------------------------------------------------------------------------
    */

    Route::get('/memorandos', [MemorandoController::class, 'create'])
        ->name('memorandos.create');

    Route::post('/memorandos', [MemorandoController::class, 'store'])
        ->name('memorandos.store');


    // Accesos directos Usuario Cliente

    Route::get('/memorandos/pase-temporal', [MemorandoController::class, 'createPaseTemporal'])
        ->name('memorandos.pase_temporal');

    Route::post('/memorandos/pase-temporal', [MemorandoController::class, 'storePaseTemporal'])
        ->name('memorandos.pase_temporal.store');


    Route::get('/memorandos/autorizacion', [MemorandoController::class, 'createAutorizacion'])
        ->name('memorandos.autorizacion');


    // Enviar autorización ingreso de equipo

    Route::post('/memorandos/enviar-autorizacion', [MemorandoController::class, 'enviarAutorizacion'])
        ->name('memorandos.enviar.autorizacion');


    // Solicitud compra

    Route::get('/memorandos/compra', [MemorandoController::class, 'createCompra'])
        ->name('memorandos.create.compra');



    // Formularios dinámicos

    Route::get('/memorandos/formulario/{tipo}', [MemorandoController::class, 'formularioDinamico'])
        ->name('memorandos.formulario');



    // Previews dinámicos

    Route::get('/memorandos/preview/{tipo}', [MemorandoController::class, 'previewDinamico'])
        ->name('memorandos.preview');



    // Histórico memorandos

    Route::get('/memorandos/historico', [MemorandoController::class, 'historico'])
        ->name('memorandos.historico');



    // Descarga PDF

    Route::get('/memorandos/{codigo}/download', [MemorandoController::class, 'download'])
        ->name('memorandos.download');




    /*
    |--------------------------------------------------------------------------
    | Solicitudes
    |--------------------------------------------------------------------------
    */

    Route::get('/solicitudes/create', [SolicitudController::class, 'create'])
        ->name('solicitudes.create');

    Route::post('/solicitudes', [SolicitudController::class, 'store'])
        ->name('solicitudes.store');

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
    [ChatbotController::class, 'warmUp']
)->name('warm-up');
        /*
         * Endpoint tradicional con respuesta JSON completa.
         */
        Route::post(
            '/message',
            [ChatbotController::class, 'message']
        )->name('message');

        /*
         * Endpoint optimizado con respuesta progresiva.
         */
        Route::post(
            '/stream',
            [ChatbotController::class, 'stream']
        )->name('stream');

        /*
         * Consultar estado de gestiones.
         */
        Route::get(
            '/estado',
            [ChatbotController::class, 'estado']
        )->name('estado');

        /*
         * Registrar si la respuesta fue útil.
         */
        Route::post(
            '/feedback',
            [ChatbotController::class, 'feedback']
        )->name('feedback');
    });


    /*
    |--------------------------------------------------------------------------
    | INCIDENCIAS / TICKETS
    |--------------------------------------------------------------------------
    |
    | Usuarios: Crear tickets, Consultar seguimiento
    | UsuarioTI: Ver cola, Tomar casos, Resolver
    | Administrador: Asignación manual
    |
    |--------------------------------------------------------------------------
    */


    Route::resource('incidencias', IncidenciaController::class);



    // Mis incidencias del usuario autenticado

    Route::get('/mis-incidencias', [IncidenciaController::class, 'misIncidencias'])
        ->name('mis-incidencias');



    // Acciones de soporte

    Route::post('/incidencias/{incidencia}/tomar', [IncidenciaController::class, 'tomar'])
        ->name('incidencias.tomar');

    Route::post('/incidencias/{incidencia}/asignar', [IncidenciaController::class, 'asignar'])
        ->name('incidencias.asignar');

    Route::post('/incidencias/{incidencia}/diagnostico', [IncidenciaController::class, 'diagnostico'])
        ->name('incidencias.diagnostico');

    Route::post('/incidencias/{incidencia}/resolver', [IncidenciaController::class, 'resolver'])
        ->name('incidencias.resolver');

    Route::post('/incidencias/{incidencia}/cerrar', [IncidenciaController::class, 'cerrar'])
        ->name('incidencias.cerrar');



});



/*
|--------------------------------------------------------------------------
| Administración
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:Administrador'])->group(function () {


    // Usuarios

    Route::resource('usuarios', UsuarioController::class);



    // Gestión administrativa incidencias

    Route::get('/administracion/incidencias', [IncidenciaController::class, 'administracion'])
        ->name('admin.incidencias');


    Route::post('/administracion/incidencias/{incidencia}/asignar', [IncidenciaController::class, 'asignar'])
        ->name('admin.incidencias.asignar');


});