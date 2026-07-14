<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class UsuarioController extends Controller
{


    public function index()
    {

        $usuarios = Usuario::with('rol')
            ->orderBy('id','desc')
            ->get();


        return view(
            'usuarios.index',
            compact('usuarios')
        );

    }





    public function create()
    {

        $roles = Rol::all();


        return view(
            'usuarios.create',
            compact('roles')
        );

    }





    public function store(Request $request)
    {


        $request->validate([


            'nombre'=>[
                'required',
                'string',
                'min:3'
            ],


            'username'=>[
                'required',
                'unique:usuarios'
            ],


            'correo'=>[
                'required',
                'email',
                'unique:usuarios'
            ],


            'password'=>[
                'required',
                'min:8'
            ],


            'rol_id'=>[
                'required',
                'exists:roles,id'
            ]


        ]);




        Usuario::create([


            'nombre'=>trim($request->nombre),

            'username'=>trim($request->username),

            'correo'=>strtolower(
                trim($request->correo)
            ),


            'password'=>Hash::make(
                $request->password
            ),


            'rol_id'=>$request->rol_id,


            'activo'=>true


        ]);



        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario creado correctamente'
            );


    }





    public function edit(
        Usuario $usuario
    )
    {

        $roles = Rol::all();


        return view(
            'usuarios.edit',
            compact(
                'usuario',
                'roles'
            )
        );

    }





    public function update(
        Request $request,
        Usuario $usuario
    )
    {


        $request->validate([


            'nombre'=>'required',

            'correo'=>[
                'required',
                'email'
            ],


            'rol_id'=>[
                'required',
                'exists:roles,id'
            ]


        ]);




        $usuario->update([

            'nombre'=>trim($request->nombre),

            'correo'=>strtolower(
                trim($request->correo)
            ),

            'rol_id'=>$request->rol_id

        ]);



        if(
            $request->filled('password')
        )
        {

            $usuario->update([

                'password'=>Hash::make(
                    $request->password
                )

            ]);

        }



        return redirect()
            ->route('usuarios.index');

    }





    public function destroy(
        Usuario $usuario
    )
    {


        $usuario->delete();


        return redirect()
            ->route('usuarios.index');

    }





    public function changeStatus(
        Usuario $usuario
    )
    {


        $usuario->update([

            'activo'=>!$usuario->activo

        ]);



        return back();

    }


}