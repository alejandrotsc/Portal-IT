<?php

namespace App\Http\Controllers;

use App\Models\Memorando;
use App\Models\MemorandoTipo;
use App\Models\SolicitudCompra;
use App\Models\MemorandoArticulo;
use App\Models\MemorandoArchivo;
use App\Models\MemorandoHistorial;
use App\Models\FolioCounter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Mail\PaseTemporalMail;

use Barryvdh\DomPDF\Facade\Pdf;


class MemorandoController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Crear memorando
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $usuario = auth()->user();
        $rol = $usuario->rol->nombre;

        $query = MemorandoTipo::where('activo', true);

        if($rol !== 'Administrador'){

            $query->where(function($q) use ($rol){

                $q->where('creado_por_rol', $rol)
                  ->orWhere('creado_por_rol', 'Todos');

            });

        }

        $tipos = $query->get();

        return view('memorandos.create', [
            'tipos' => $tipos,
            'tipoInicial' => null
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Crear pase menor a 24 horas
    |--------------------------------------------------------------------------
    */

    public function createPaseTemporal()
{
    $tipoPase = MemorandoTipo::where('formulario', 'pase_temporal')
        ->where('activo', true)
        ->firstOrFail();

    return view('memorandos.pase_temporal.create', [
        'tipoPase' => $tipoPase
    ]);
}



    /*
    |--------------------------------------------------------------------------
    | Crear memorando autorización
    |--------------------------------------------------------------------------
    */

    public function createAutorizacion()
{
    $tipoAutorizacion = MemorandoTipo::where('formulario', 'autorizacion')
        ->where('activo', true)
        ->firstOrFail();

    return view('memorandos.autorizacion.create', [
        'tipoAutorizacion' => $tipoAutorizacion
    ]);
}



/*
|--------------------------------------------------------------------------
| Enviar pase temporal por correo
|--------------------------------------------------------------------------
*/

public function storePaseTemporal(Request $request)
{
    DB::beginTransaction();

    try {


        $request->validate([

            'tipo_id' => [
                'required',
                'exists:memorando_tipos,id'
            ],

            'de_nombre' => [
                'required',
                'string'
            ],

            'asunto' => [
                'required',
                'string'
            ],

            'fecha_documento' => [
                'required',
                'date'
            ],

            'colaborador' => [
                'required',
                'string'
            ],

            'cargo_area' => [
                'required',
                'string'
            ],

            'motivo_autorizacion' => [
                'required',
                'string'
            ],

        ]);



        $usuario = auth()->user();


        $tipo = MemorandoTipo::findOrFail(
            $request->tipo_id
        );





        /*
        |--------------------------------------------------------------------------
        | Guardar datos dinámicos
        |--------------------------------------------------------------------------
        */

        $datosExtra = $request->except([

            '_token',

            'tipo_id'

        ]);







        /*
        |--------------------------------------------------------------------------
        | Crear memorando
        |--------------------------------------------------------------------------
        */

        $memorando = Memorando::create([


            'tipo_id' => $tipo->id,


            'solicitante_id' => $usuario->id,


            'estado' => Memorando::ESTADO_GENERADO,



            'para_nombre' => 
                $request->para_nombre,



            'cc_nombre' => 
                $request->cc_nombre,



            'de_nombre' => 
                $request->de_nombre,



            'asunto' => 
                $request->asunto,



            'observaciones' => 
                $request->observaciones,



            'fecha_documento' =>
                $request->fecha_documento,



            'datos_extra' =>
                $datosExtra,


        ]);








        /*
        |--------------------------------------------------------------------------
        | Histórico
        |--------------------------------------------------------------------------
        */

        MemorandoHistorial::create([


            'memorando_id' =>
                $memorando->id,


            'usuario_id' =>
                $usuario->id,


            'estado_anterior' =>
                null,


            'estado_nuevo' =>
                Memorando::ESTADO_GENERADO,


            'comentario' =>
                'Pase temporal enviado a Helpdesk',

        ]);









        /*
        |--------------------------------------------------------------------------
        | Cargar relaciones para correo
        |--------------------------------------------------------------------------
        */

        $memorando->load([

            'tipo',

            'solicitante'

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

            new PaseTemporalMail(

                $memorando

            )

        );

        DB::commit();

        return response()->json([


            'success'=>true,


            'message'=>
                'La solicitud del pase menor a 24 horas fue enviada correctamente.',


            'id'=>
                $memorando->id


        ]);







    } catch(\Exception $e) {


        DB::rollBack();



        Log::error(
            'Error enviando pase temporal',
            [
                'error'=>$e->getMessage()
            ]
        );



        return response()->json([


            'success'=>false,


            'error'=>$e->getMessage()


        ],500);



    }
}




    /*
    |--------------------------------------------------------------------------
    | Crear compra
    |--------------------------------------------------------------------------
    */


    public function createCompra()
    {
        $usuario = auth()->user();
        $rol = $usuario->rol->nombre;

        $query = MemorandoTipo::where('activo', true);

        if($rol !== 'Administrador'){

            $query->where(function($q) use ($rol){

                $q->where('creado_por_rol', $rol)
                  ->orWhere('creado_por_rol', 'Todos');

            });

        }

        $tipos = $query->get();

        return view('memorandos.create_compra', compact('tipos'));
    }

    /*
    |--------------------------------------------------------------------------
    | Carga dinámica de vistas de preview
    |--------------------------------------------------------------------------
    */

    public function previewDinamico($tipo)
    {
        return view("memorandos.previews.{$tipo}");
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar memorando
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        DB::beginTransaction();

        try{

            $request->validate([
    'tipo_id'=>[
        'required',
        'exists:memorando_tipos,id'
    ],

    'de_nombre'=>[
        'required',
        'string'
    ],

    'asunto'=>[
        'required',
        'string'
    ],

    'fecha_documento'=>[
        'required',
        'date'
    ]
]);

            $usuario = auth()->user();

            $tipo = MemorandoTipo::findOrFail(
                $request->tipo_id
            );

            $rol = $usuario->rol->nombre;


            /*
            |--------------------------------------------------------------------------
            | Validación permisos
            |--------------------------------------------------------------------------
            */

            if($rol !== 'Administrador'){

                $permitido =
                    $tipo->creado_por_rol === 'Todos' ||
                    $tipo->creado_por_rol === $rol;

                if(!$permitido){

                    return response()->json([
                        'success'=>false,
                        'error'=>'No tiene permisos para generar este documento.'
                    ],403);

                }

            }



            /*
            |--------------------------------------------------------------------------
            | Generar Folio si aplica
            |--------------------------------------------------------------------------
            */

            $codigo = null;

            if($tipo->requiere_folio){

                $codigo = $this->generarFolio('DIT');

            }



            /*
            |--------------------------------------------------------------------------
            | Datos adicionales
            |--------------------------------------------------------------------------
            */

            $datosExtra = $request->except([
                '_token',
                'tipo_id'
            ]);




            /*
            |--------------------------------------------------------------------------
            | Crear memorando
            |--------------------------------------------------------------------------
            */

            $memorando = Memorando::create([

                'codigo'=>$codigo,

                'tipo_id'=>$tipo->id,

                'solicitante_id'=>$usuario->id,

                'estado'=>Memorando::ESTADO_GENERADO,

                'para_nombre'=>$request->para_nombre,

                'cc_nombre'=>$request->cc_nombre,

                'de_nombre'=>$request->de_nombre,

                'asunto'=>$request->asunto,

                'observaciones'=>$request->observaciones,

                'fecha_documento'=>$request->fecha_documento,

                'datos_extra'=>$datosExtra

            ]);




            /*
|--------------------------------------------------------------------------
| Datos específicos
|--------------------------------------------------------------------------
*/

$this->guardarDatosFormulario(
    $request,
    $tipo,
    $memorando
);



/*
|--------------------------------------------------------------------------
| Recargar memorando con datos actualizados
|--------------------------------------------------------------------------
|
| guardarDatosFormulario() actualiza datos_extra para formularios
| dinámicos como autorización y pase temporal.
| Se vuelve a consultar para que el PDF reciba la información real.
|
*/

$memorando = Memorando::with([
    'tipo',
    'solicitante',
    'solicitudCompra',
    'articulos'
])
->findOrFail($memorando->id);



/*
|--------------------------------------------------------------------------
| Generar PDF
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Generar PDF
|--------------------------------------------------------------------------
*/

$memorando->refresh();

$memorando->load([
    'tipo',
    'solicitante',
    'articulos'
]);


$pdf = Pdf::loadView(
    'memorandos.pdf',
    [
        'memorando' => $memorando
    ]
);


$nombrePdf = 
    ($memorando->codigo 
        ?? 'MEM-'.$memorando->id.'-'.now()->year
    )
    . '.pdf';



$rutaPdf =
    'documentos-it/memorandos/pdf/'.$nombrePdf;



Storage::put(
    $rutaPdf,
    $pdf->output()
);



$memorando->update([
    'archivo_pdf'=>$rutaPdf
]);




            /*
            |--------------------------------------------------------------------------
            | Registrar archivo
            |--------------------------------------------------------------------------
            */

            MemorandoArchivo::create([

                'memorando_id'=>$memorando->id,

                'tipo_archivo'=>'PDF_GENERADO',

                'nombre_archivo'=>$nombrePdf,

                'ruta_archivo'=>$rutaPdf,

                'cargado_por'=>$usuario->id

            ]);




            /*
            |--------------------------------------------------------------------------
            | Historial
            |--------------------------------------------------------------------------
            */

            MemorandoHistorial::create([

                'memorando_id'=>$memorando->id,

                'usuario_id'=>$usuario->id,

                'estado_anterior'=>null,

                'estado_nuevo'=>Memorando::ESTADO_GENERADO,

                'comentario'=>'Memorando generado correctamente'

            ]);



            DB::commit();


            return response()->json([

                'success'=>true,

                'codigo'=>$memorando->codigo,

                'id'=>$memorando->id,

                'download'=>route(
                    'memorandos.download',
                    $memorando->id
                )

            ]);

        }
        catch(\Exception $e){

            DB::rollBack();

            return response()->json([

                'success'=>false,

                'error'=>$e->getMessage()

            ],500);

        }
    }

        /*
    |--------------------------------------------------------------------------
    | Guardar información según formulario
    |--------------------------------------------------------------------------
    */

    private function guardarDatosFormulario(
        Request $request,
        MemorandoTipo $tipo,
        Memorando $memorando
    ){

        switch($tipo->formulario){

            /*
            |--------------------------------------------------------------------------
            | Solicitudes de compra
            |--------------------------------------------------------------------------
            */

            case 'orden_pago':
            case 'contratacion_servicio':
            case 'renovacion_servicio':
            case 'repuestos':
            case 'accesorios':

                if($request->empresa || $request->tipo_compra){

                    $request->validate([

                        'empresa'=>[
                            'required',
                            'string',
                            'max:200'
                        ],

                        'tipo_compra'=>[
                            'required',
                            'string',
                            'max:100'
                        ],

                        'motivo_compra'=>[
                            'required',
                            'string',
                            'max:100'
                        ]

                    ]);


                    SolicitudCompra::create([

                        'memorando_id'=>$memorando->id,

                        'empresa'=>$request->empresa,

                        'tipo_compra'=>$request->tipo_compra,

                        'motivo_compra'=>$request->motivo_compra,

                        'proveedor'=>$request->proveedor,

                        'razon_proveedor'=>$request->razon_proveedor

                    ]);

                }

            break;



            /*
|--------------------------------------------------------------------------
| Equipos tecnológicos
|--------------------------------------------------------------------------
*/

case 'laptop':
case 'desktop':
case 'monitor':


    $datosExtra = $memorando->datos_extra ?? [];


    $datosExtra['equipos'] = $request->equipos ?? [];


    $memorando->update([
        'datos_extra' => $datosExtra
    ]);



    foreach($request->equipos ?? [] as $equipo){

        MemorandoArticulo::create([

            'memorando_id'=>$memorando->id,

            'codigo'=>$equipo['codigo'] ?? null,

            'descripcion'=>$equipo['descripcion'] ?? null,

            'unidad'=>$equipo['unidad'] ?? 'Unidad',

            'cantidad'=>$equipo['cantidad'] ?? 1

        ]);

    }


break;



            /*
            |--------------------------------------------------------------------------
            | Pase temporal / Autorización
            |--------------------------------------------------------------------------
            */

            case 'pase_temporal':
            case 'autorizacion':

                $memorando->update([

                    'datos_extra'=>$request->except([
                        '_token',
                        'tipo_id'
                    ])

                ]);

            break;

        }

    }





    /*
    |--------------------------------------------------------------------------
    | Generar folio DIT
    |--------------------------------------------------------------------------
    */

    private function generarFolio($prefijo)
    {
        $contador = FolioCounter::where(
            'prefijo',
            $prefijo
        )
        ->lockForUpdate()
        ->firstOrFail();


        $contador->increment(
            'ultimo_valor'
        );


        return sprintf(
            '%s-%03d-%d-%d',
            $prefijo,
            $contador->ultimo_valor,
            now()->month,
            now()->year
        );
    }





    /*
    |--------------------------------------------------------------------------
    | Histórico de memorandos
    |--------------------------------------------------------------------------
    */

    public function historico()
    {
        $memorandos = Memorando::with([
            'tipo',
            'solicitante'
        ])
        ->latest()
        ->get();


        return view(
            'memorandos.historico',
            compact('memorandos')
        );
    }





    /*
    |--------------------------------------------------------------------------
    | Descargar PDF
    |--------------------------------------------------------------------------
    */

    public function download($id)
    {
        $memorando = Memorando::findOrFail($id);


        if(
            !$memorando->archivo_pdf ||
            !Storage::exists($memorando->archivo_pdf)
        ){

            abort(404);

        }


        return Storage::download(
            $memorando->archivo_pdf
        );
    }





    /*
    |--------------------------------------------------------------------------
    | Visualizar PDF
    |--------------------------------------------------------------------------
    */

    public function pdf($id)
    {
        $memorando = Memorando::findOrFail($id);


        if(
            !$memorando->archivo_pdf ||
            !Storage::exists($memorando->archivo_pdf)
        ){

            abort(404);

        }


        return response()->file(
            storage_path(
                'app/'.$memorando->archivo_pdf
            )
        );
    }

}