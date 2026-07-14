<?php

namespace App\Http\Controllers;


use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistroRequest;


use App\Models\Rol;
use App\Models\Usuario;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



class AuthController extends Controller
{


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */


    public function login()
    {

        return view('auth.login');

    }






    public function authenticate(LoginRequest $request)
    {


        $usuario = Usuario::where(

                'username',

                $request->login

            )
            ->orWhere(

                'correo',

                $request->login

            )
            ->first();







        if (!$usuario)
        {

            return back()

                ->withInput([

                    'login' => $request->login

                ])

                ->withErrors([

                    'login' => 
                    'Credenciales incorrectas.'

                ]);

        }









        if (!$usuario->activo)
        {

            return back()

                ->withInput([

                    'login' => $request->login

                ])

                ->withErrors([

                    'login' =>
                    'El usuario se encuentra desactivado.'

                ]);

        }









        if (!Hash::check(

            $request->password,

            $usuario->password

        ))
        {


            return back()

                ->withInput([

                    'login' => $request->login

                ])

                ->withErrors([

                    'login' =>
                    'Credenciales incorrectas.'

                ]);

        }









        Auth::login($usuario);




        $request->session()
            ->regenerate();







        return redirect()

            ->route('dashboard');


    }









    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */


    public function logout()
    {


        Auth::logout();


        request()

            ->session()

            ->invalidate();

        request()

            ->session()

            ->regenerateToken();

        return redirect()

            ->route('login');
    }









    /*
    |--------------------------------------------------------------------------
    | REGISTRO
    |--------------------------------------------------------------------------
    */



    public function register()
    {

        return view('auth.register');

    }









    public function store(RegistroRequest $request)
    {



        $rolUsuario = Rol::where(

            'nombre',

            'Usuario'

        )->first();







        if (!$rolUsuario)
        {

            return back()

                ->withErrors([

                    'registro' =>
                    'No existe un rol predeterminado para usuarios.'

                ]);

        }









        Usuario::create([


            'nombre' =>

                $request->nombre,



            'username' =>

                $request->username,



            'correo' =>

                $request->correo,



            'password' =>

                Hash::make(

                    $request->password

                ),



            'rol_id' =>

                $rolUsuario->id,



            'activo' => true,


        ]);









        return redirect()

            ->route('login')

            ->with(

                'success',

                'Usuario creado correctamente.'

            );


    }









    /*
    |--------------------------------------------------------------------------
    | RECUPERACIÓN DE CONTRASEÑA
    |--------------------------------------------------------------------------
    */


    public function forgotPassword()
    {

        return view('auth.forgot-password');

    }









    public function sendResetLink(
        ForgotPasswordRequest $request
    )
    {


        $usuario = Usuario::where(

            'correo',

            $request->correo

        )->first();









        /*
        |--------------------------------------------------------------------------
        | No revelar existencia del correo
        |--------------------------------------------------------------------------
        */



        if (!$usuario)
        {

            return back()

                ->with(

                    'success',

                    'Si el correo está registrado recibirá instrucciones para recuperar la contraseña.'

                );

        }









        /*
        |--------------------------------------------------------------------------
        | Pendiente implementación:
        |
        | - Crear token seguro
        | - Guardar expiración
        | - Enviar correo
        | - Registrar auditoría
        |--------------------------------------------------------------------------
        */







        return back()

            ->with(

                'success',

                'Se enviaron instrucciones para recuperar la contraseña.'

            );


    }



}