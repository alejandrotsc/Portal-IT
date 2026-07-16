<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SolicitudMail;

class SolicitudController extends Controller
{

    public function create()
    {
        return view('solicitudes.create');
    }




    public function store(Request $request)
    {


        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */


        $request->validate([

            'categoria'=>[
                'required',
                'string'
            ],

            'asunto'=>[
                'required',
                'string',
                'max:255'
            ],

            'descripcion'=>[
                'required',
                'string'
            ],

        ]);





        /*
        |--------------------------------------------------------------------------
        | Generar folio
        |--------------------------------------------------------------------------
        */


        $ultima = Solicitud::orderBy('id','desc')
            ->first();



        $numero = $ultima
            ? intval(substr($ultima->folio,4)) + 1
            : 1;



        $folio = 'SOL-'.str_pad(
            $numero,
            5,
            '0',
            STR_PAD_LEFT
        );







        /*
        |--------------------------------------------------------------------------
        | Datos dinámicos
        |--------------------------------------------------------------------------
        */


        $datosExtra = $request->except([

            '_token',
            'categoria',
            'asunto',
            'descripcion'

        ]);







        /*
        |--------------------------------------------------------------------------
        | Crear solicitud
        |--------------------------------------------------------------------------
        */


        $solicitud = Solicitud::create([


            'folio'=>$folio,


            'usuario_id'=>Auth::id(),


            'categoria'=>$request->categoria,


            'asunto'=>$request->asunto,


            'descripcion'=>$request->descripcion,


            'datos_extra'=>$datosExtra ?: null,


            'correo_enviado'=>false,


            'correo_enviado_at'=>null


        ]);









        /*
        |--------------------------------------------------------------------------
        | Enviar correo
        |--------------------------------------------------------------------------
        */


        Mail::to(

            'alejandrotsc01@gmail.com'

        )
        ->send(

            new SolicitudMail(
                $solicitud
            )

        );









        /*
        |--------------------------------------------------------------------------
        | Actualizar envío
        |--------------------------------------------------------------------------
        */


        $solicitud->update([


            'correo_enviado'=>true,


            'correo_enviado_at'=>now()


        ]);









        return redirect()

            ->route('solicitudes.create')

            ->with(

                'success',
                'Solicitud enviada correctamente.'

            );


    }


}