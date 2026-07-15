<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use App\Models\IncidenciaArchivo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use App\Mail\IncidenciaMail;
use App\Services\OcrService;


class IncidenciaController extends Controller
{


    public function index()
    {

        $incidencias = Incidencia::with([
            'usuario',
            'archivos'
        ])
        ->latest()
        ->get();


        return view(
            'incidencias.index',
            compact('incidencias')
        );

    }





    public function create()
    {

        return view(
            'incidencias.create'
        );

    }







    public function store(
        Request $request,
        OcrService $ocr
    )
    {


        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */


        $request->validate([


            'titulo'=>[
                'required',
                'string',
                'max:255'
            ],


            'descripcion'=>[
                'required',
                'string'
            ],


            'tiempo_problema'=>[
                'nullable',
                'string'
            ],


            'afectacion'=>[
                'nullable',
                'string'
            ],


            'equipo'=>[
                'nullable',
                'string'
            ],


            'ubicacion'=>[
                'nullable',
                'string'
            ],


            'archivos'=>[
                'nullable',
                'array'
            ],


            'archivos.*'=>[
                'image',
                'max:10240'
            ]

        ]);









        /*
        |--------------------------------------------------------------------------
        | Generar código
        |--------------------------------------------------------------------------
        */


        $ultima = Incidencia::orderBy('id','desc')
    ->first();


$numero = $ultima
    ? intval(substr($ultima->codigo,4)) + 1
    : 1;



        $codigo = 'INC-'.str_pad(
            $numero,
            5,
            '0',
            STR_PAD_LEFT
        );









        /*
        |--------------------------------------------------------------------------
        | Crear incidencia
        |--------------------------------------------------------------------------
        */


        $incidencia = Incidencia::create([


            'codigo'=>$codigo,


            'usuario_id'=>Auth::id(),


            'titulo'=>$request->titulo,


            'descripcion'=>$request->descripcion,


            'tiempo_problema'=>$request->tiempo_problema,


            'afectacion'=>$request->afectacion,


            'equipo'=>$request->equipo,


            'ubicacion'=>$request->ubicacion,


            'estado'=>'Abierta',


            'prioridad'=>'Media',


            'correo_enviado'=>false


        ]);









        /*
        |--------------------------------------------------------------------------
        | Guardar archivos + OCR
        |--------------------------------------------------------------------------
        */


        $textoOCR = [];



        if($request->hasFile('archivos')){


            foreach($request->file('archivos') as $archivo){



                /*
                Guardar imagen
                */


                $ruta = $archivo->store(
                    'incidencias',
                    'public'
                );




                /*
                Ejecutar OCR
                */


                $texto = $ocr->leerImagen(

                    storage_path(
                        'app/public/'.$ruta
                    )

                );



                $textoOCR[] = $texto;






                /*
                Guardar archivo
                */


                IncidenciaArchivo::create([


                    'incidencia_id'=>$incidencia->id,


                    'usuario_id'=>Auth::id(),


                    'nombre_original'=>$archivo->getClientOriginalName(),


                    'nombre_archivo'=>basename($ruta),


                    'ruta'=>$ruta,


                    'extension'=>$archivo->getClientOriginalExtension(),


                    'tamano'=>$archivo->getSize(),


                    'texto_ocr'=>$texto ?: null


                ]);



            }

        }









        /*
        |--------------------------------------------------------------------------
        | Enviar correo
        |--------------------------------------------------------------------------
        */


        Mail::to(
            'alejandrotsc01@gmail.com'
        )
        ->send(
            new IncidenciaMail(
                $incidencia,
                $textoOCR
            )
        );




        $incidencia->update([


            'correo_enviado'=>true,


            'fecha_envio_correo'=>now()


        ]);









        return redirect()

            ->route(
                'incidencias.show',
                $incidencia
            )

            ->with(
                'success',
                'Incidencia enviada correctamente.'
            );

    }

    public function misIncidencias()
{

    $incidencias = Incidencia::where(
        'usuario_id',
        Auth::id()
    )
    ->with('archivos')
    ->latest()
    ->get();


    return view(
        'incidencias.mis-incidencias',
        compact('incidencias')
    );

}









    public function show(
        Incidencia $incidencia
    )
    {


        $incidencia->load([

            'usuario',

            'archivos'

        ]);



        return view(
            'incidencias.show',
            compact('incidencia')
        );


    }








    public function cerrar(
        Incidencia $incidencia
    )
    {


        $incidencia->update([

            'estado'=>'Cerrada'

        ]);


        return back();

    }


}