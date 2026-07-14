<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MemorandoController;
use App\Http\Controllers\DashboardController;


/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/


Route::get('/login', [AuthController::class, 'login'])
    ->name('login');


Route::post('/login', [AuthController::class, 'authenticate'])
    ->name('login.authenticate');




Route::get('/registro', [AuthController::class, 'register'])
    ->name('register');


Route::post('/registro', [AuthController::class, 'store'])
    ->name('register.store');




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



    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */


    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');





    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */


    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');






    /*
    |--------------------------------------------------------------------------
    | MEMORANDOS
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/memorandos',
        [MemorandoController::class,'create']
    )
    ->name('memorandos.create');





    Route::post(
        '/memorandos',
        [MemorandoController::class,'store']
    )
    ->name('memorandos.store');


    /*
|--------------------------------------------------------------------------
| Accesos directos Usuario Cliente
|--------------------------------------------------------------------------
*/

Route::get(
    '/memorandos/pase-temporal',
    [MemorandoController::class,'createPaseTemporal']
)
->name('memorandos.pase_temporal');


Route::get(
    '/memorandos/autorizacion',
    [MemorandoController::class,'createAutorizacion']
)
->name('memorandos.autorizacion');





    /*
    |--------------------------------------------------------------------------
    | Solicitudes de compra
    |--------------------------------------------------------------------------
    |
    | Temporalmente separada.
    | Luego será absorbida por create().
    |
    */


     Route::get(
        '/memorandos/compra',
        [MemorandoController::class,'createCompra']
    )
    ->name('memorandos.create.compra');
   





    /*
    |--------------------------------------------------------------------------
    | CARGA DINÁMICA DE FORMULARIOS
    |--------------------------------------------------------------------------
    |
    | Ejemplo:
    |
    | /memorandos/formulario/laptop
    |
    | devuelve:
    | resources/views/memorandos/formularios/laptop.blade.php
    |
    */


    Route::get(
        '/memorandos/formulario/{tipo}',
        [MemorandoController::class,'formularioDinamico']
    )
    ->name('memorandos.formulario');






    /*
    |--------------------------------------------------------------------------
    | CARGA DINÁMICA DE PREVIEWS
    |--------------------------------------------------------------------------
    |
    | Ejemplo:
    |
    | /memorandos/preview/laptop
    |
    | devuelve:
    | resources/views/memorandos/previews/laptop.blade.php
    |
    */

    Route::get('/memorandos/formulario/{tipo}', [MemorandoController::class, 'formularioDinamico']);
Route::get('/memorandos/preview/{tipo}', [MemorandoController::class, 'previewDinamico']);


    Route::get(
        '/memorandos/preview/{tipo}',
        [MemorandoController::class,'previewDinamico']
    )
    ->name('memorandos.preview');







    /*
    |--------------------------------------------------------------------------
    | Histórico
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/memorandos/historico',
        [MemorandoController::class,'historico']
    )
    ->name('memorandos.historico');





    /*
    |--------------------------------------------------------------------------
    | Descarga PDF
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/memorandos/{codigo}/download',
        [MemorandoController::class,'download']
    )
    ->name('memorandos.download');




});






/*
|--------------------------------------------------------------------------
| Administración
|--------------------------------------------------------------------------
*/


Route::middleware([
    'auth',
    'rol:Administrador'

])
->group(function () {



    Route::resource(
        'usuarios',
        UsuarioController::class
    );



});