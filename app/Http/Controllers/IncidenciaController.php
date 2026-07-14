<?php


namespace App\Http\Controllers;


use App\Models\Incidencia;
use App\Models\IncidenciaArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class IncidenciaController extends Controller
{


    public function index()
    {

        $incidencias = Incidencia::with([
            'usuario',
            'tecnico'
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







    public function store(Request $request)
    {



        $request->validate([


            'titulo'=>'required',
            'categoria'=>'required',
            'descripcion'=>'required',


            'archivos.*'=>
            'nullable|image|max:4096'


        ]);




        $ultima = Incidencia::latest()->first();



        $numero = $ultima
            ? $ultima->id + 1
            : 1;




        $codigo =
            'INC-'.
            str_pad(
                $numero,
                5,
                '0',
                STR_PAD_LEFT
            );






        $incidencia =
        Incidencia::create([


            'codigo'=>$codigo,


            'usuario_id'=>Auth::id(),


            'titulo'=>$request->titulo,


            'categoria'=>$request->categoria,


            'descripcion'=>$request->descripcion,


            'prioridad'=>$request->prioridad
                ?? 'media',


            'estado'=>'abierta'


        ]);








        if($request->hasFile('archivos')){


            foreach(
                $request->file('archivos')
                as $archivo
            ){


                $path =
                $archivo->store(
                    'incidencias',
                    'public'
                );



                IncidenciaArchivo::create([


                    'incidencia_id'=>$incidencia->id,


                    'archivo'=>$path,


                    'nombre_original'=>
                    $archivo->getClientOriginalName(),


                    'tipo'=>
                    $archivo->getMimeType()


                ]);


            }


        }






        return redirect()

            ->route('incidencias.show',$incidencia)

            ->with(
                'success',
                'Incidencia creada correctamente'
            );


    }










    public function show(
        Incidencia $incidencia
    )
    {


        $incidencia->load([
            'usuario',
            'tecnico',
            'archivos'
        ]);



        return view(
            'incidencias.show',
            compact('incidencia')
        );


    }









    public function asignar(
        Request $request,
        Incidencia $incidencia
    )
    {


        $request->validate([

            'tecnico_id'=>'required'

        ]);



        $incidencia->update([


            'tecnico_id'=>
            $request->tecnico_id,


            'estado'=>'asignada'


        ]);



        return back();


    }









    public function diagnostico(
        Request $request,
        Incidencia $incidencia
    )
    {


        $request->validate([

            'diagnostico'=>'required'

        ]);




        $incidencia->update([


            'diagnostico'=>
            $request->diagnostico,


            'estado'=>
            'diagnostico'


        ]);



        return back();


    }










    public function resolver(
        Request $request,
        Incidencia $incidencia
    )
    {



        $request->validate([

            'solucion'=>'required'

        ]);



        $incidencia->update([


            'solucion'=>
            $request->solucion,


            'estado'=>'resuelta',


            'fecha_resuelto'=>
            now()


        ]);



        return back();


    }









    public function cerrar(
        Incidencia $incidencia
    )
    {


        $incidencia->update([

            'estado'=>'cerrada'

        ]);



        return back();


    }




}