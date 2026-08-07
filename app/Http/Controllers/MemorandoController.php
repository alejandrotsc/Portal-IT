<?php

namespace App\Http\Controllers;

use App\Models\Memorando;
use App\Models\MemorandoTipo;
use App\Models\SolicitudCompra;
use App\Models\MemorandoArticulo;
use App\Models\MemorandoArchivo;
use App\Models\MemorandoHistorial;
use App\Models\FolioCounter;
use App\Models\Usuario;
use App\Services\Mail\TrackedMailService;
use App\Notifications\EstadoPaseActualizadoNotification;
use App\Notifications\NuevoPaseNotification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use App\Mail\PaseTemporalMail;
use App\Mail\AutorizacionMail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


/*
|--------------------------------------------------------------------------
| Controlador de memorandos
|--------------------------------------------------------------------------
|
| Gestiona los distintos flujos documentales del Portal TI: memorandos,
| pases menores y mayores a 24 horas, solicitudes de compra, generación de
| folios y PDF, historial, notificaciones y administración de estados.
|
*/

class MemorandoController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Crear memorando
    |--------------------------------------------------------------------------
    |
    | Registra los datos principales del memorando y lo deja inicialmente en estado generado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar formulario general
    |--------------------------------------------------------------------------
    |
    | Determina los tipos de memorando visibles según el rol autenticado y
    | prepara la pantalla general de creación.
    |
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
    |
    | Obtiene el tipo activo correspondiente al pase temporal y presenta su formulario específico.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar formulario de pase temporal
    |--------------------------------------------------------------------------
    |
    | Recupera el tipo activo correspondiente y renderiza el formulario
    | específico para pases menores a 24 horas.
    |
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
    |
    | Obtiene el tipo activo de autorización y presenta el formulario utilizado para pases mayores a 24 horas.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar formulario de autorización
    |--------------------------------------------------------------------------
    |
    | Recupera el tipo activo de autorización y renderiza el formulario de
    | pase mayor a 24 horas.
    |
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
| Enviar pase menor a 24 horas por correo
|--------------------------------------------------------------------------
|
| Valida, registra y envía el pase temporal a Helpdesk dentro de una transacción, conservando el memorando aunque el correo quede pendiente.
|
*/

/*
|--------------------------------------------------------------------------
| Registrar pase temporal
|--------------------------------------------------------------------------
|
| Ejecuta el flujo transaccional de creación, historial, correo con seguimiento
| y notificación administrativa del pase menor a 24 horas.
|
*/

public function storePaseTemporal(
    Request $request,
    TrackedMailService $trackedMail
)
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
        | Enviar correo con seguimiento
        |--------------------------------------------------------------------------
        |
        | TrackedMailService captura cualquier falla SMTP. Por lo tanto, el
        | memorando se confirma aunque la notificación no pueda enviarse.
        |
        */

        $delivery = $trackedMail->sendAsync(
            emailable: $memorando,

            mailable: new PaseTemporalMail(
                $memorando
            ),

            recipientEmail:
                'helpdesk@televicentro.hn',

            mailType:
                'pase_temporal_creado',

            recipientName:
                'Equipo de soporte TI',

            subject:
                'Nuevo pase menor a 24 horas',

            metadata: [
                'tipo_id' =>
                    $memorando->tipo_id,

                'solicitante_id' =>
                    $memorando->solicitante_id,

                'tipo_documento' =>
                    'pase_temporal',
            ]
        );

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Notificar nuevo pase a los administradores
        |--------------------------------------------------------------------------
        */

        $this->notificarNuevoPase(
            $memorando
        );

        return response()->json([


            'success'=>true,


            'message'=>
                $delivery->estaPendiente()
                    ? 'La solicitud del pase menor a 24 horas fue registrada correctamente. La notificación por correo se está procesando.'
                    : 'La solicitud del pase menor a 24 horas fue registrada correctamente, pero no fue posible colocar la notificación en la cola de correo.',


            'id'=>
                $memorando->id,

            'email'=>[
                'sent'=>
                    false,

                'queued'=>
                    $delivery->estaPendiente(),

                'status'=>
                    $delivery->status,

                'delivery_id'=>
                    $delivery->id,
            ]


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
    |
    | Prepara el formulario general de compra filtrando los tipos de memorando permitidos según el rol.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Mostrar formulario de compra
    |--------------------------------------------------------------------------
    |
    | Filtra los tipos disponibles según el rol y prepara la vista utilizada
    | para solicitudes relacionadas con compras y servicios.
    |
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
    |
    | Carga la vista de preview correspondiente al tipo solicitado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar preview dinámico
    |--------------------------------------------------------------------------
    |
    | Resuelve la vista parcial correspondiente al tipo solicitado para que el
    | frontend pueda cargarla de forma asíncrona.
    |
    */

    public function previewDinamico($tipo)
    {
        return view("memorandos.previews.{$tipo}");
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar memorando
    |--------------------------------------------------------------------------
    |
    | Procesa memorandos generales, valida permisos, genera folio cuando corresponde, guarda datos específicos y genera el PDF.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Registrar memorando general
    |--------------------------------------------------------------------------
    |
    | Valida permisos y datos, genera folio si corresponde, guarda información
    | específica, crea el PDF y procesa correo e historial.
    |
    */

    public function store(
    Request $request,
    TrackedMailService $trackedMail)
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
            |
            | Comprueba que el tipo de memorando pueda ser generado por el rol autenticado.
            |
            */

            if($rol !== 'Administrador'){

                $permitido =
                    $tipo->creado_por_rol === 'Todos' ||
                    $tipo->creado_por_rol === $rol;

                if (! $permitido) {
    DB::rollBack();

    return response()->json([
        'success' => false,
        'error' => 'No tiene permisos para generar este documento.',
    ], 403);
}

            }



            /*
            |--------------------------------------------------------------------------
            | Generar Folio si aplica
            |--------------------------------------------------------------------------
            |
            | Genera un folio DIT únicamente para tipos configurados como documentos con consecutivo.
            |
            */

            $codigo = null;

            if($tipo->requiere_folio){

                $codigo = $this->generarFolio('DIT');

            }



            /*
            |--------------------------------------------------------------------------
            | Datos adicionales
            |--------------------------------------------------------------------------
            |
            | Captura los campos dinámicos del formulario para conservar información no incluida en columnas principales.
            |
            */

            $datosExtra = $request->except([
                '_token',
                'tipo_id'
            ]);




            /*
            |--------------------------------------------------------------------------
            | Crear memorando
            |--------------------------------------------------------------------------
            |
            | Registra los datos principales del memorando y lo deja inicialmente en estado generado.
            |
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
|
| Delegar el almacenamiento de información particular al método asociado al formulario del memorando.
|
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
|
| Renderiza el memorando mediante DomPDF, almacena el archivo y asocia su ruta al registro.
|
*/

/*
|--------------------------------------------------------------------------
| Generar PDF
|--------------------------------------------------------------------------
|
| Renderiza el memorando mediante DomPDF, almacena el archivo y asocia su ruta al registro.
|
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
            |
            | Registra el PDF generado dentro de los archivos asociados al memorando.
            |
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



            /*
|--------------------------------------------------------------------------
| Enviar pase mayor a 24 horas por correo
|--------------------------------------------------------------------------
|
| Solamente se envía cuando el formulario corresponde a una
| autorización o pase mayor a 24 horas.
|
*/

$delivery = null;

if ($tipo->formulario === 'autorizacion') {
    /*
     * Actualizar el modelo para que AutorizacionMail pueda encontrar
     * el PDF recién generado y adjuntarlo.
     */
    $memorando->refresh();

    $memorando->load([
        'tipo',
        'solicitante',
    ]);

    $delivery = $trackedMail->sendAsync(
        emailable:
            $memorando,

        mailable:
            new AutorizacionMail(
                $memorando
            ),

        recipientEmail:
            'helpdesk@televicentro.hn',

        mailType:
            'pase_mayor_creado',

        recipientName:
            'Equipo de soporte TI',

        subject:
            'Pase mayor a 24 horas pendiente de firma',

        metadata: [
            'codigo' =>
                $memorando->codigo,

            'tipo_id' =>
                $memorando->tipo_id,

            'solicitante_id' =>
                $memorando->solicitante_id,

            'tipo_documento' =>
                'autorizacion',

            'archivo_pdf' =>
                $memorando->archivo_pdf,
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Confirmar información
|--------------------------------------------------------------------------
|
| TrackedMailService captura las fallas SMTP. Por eso una falla de correo
| no provoca que se elimine el memorando.
|
*/

DB::commit();

/*
|--------------------------------------------------------------------------
| Notificar nuevo pase a los administradores
|--------------------------------------------------------------------------
|
| Este método store() también procesa otros memorandos. Por eso únicamente
| se notifica cuando el formulario corresponde a una autorización.
|
*/

if ($tipo->formulario === 'autorizacion') {
    $this->notificarNuevoPase(
        $memorando
    );
}

/*
|--------------------------------------------------------------------------
| Construir respuesta
|--------------------------------------------------------------------------
|
| Genera la respuesta JSON con identificadores, ruta de descarga y estado del correo cuando aplica.
|
*/

$response = [
    'success' =>
        true,

    'codigo' =>
        $memorando->codigo,

    'id' =>
        $memorando->id,

    'download' =>
        route(
            'memorandos.download',
            $memorando->id
        ),

    'message' =>
        'El documento fue generado correctamente.',
];

/*
|--------------------------------------------------------------------------
| Agregar resultado SMTP solamente para autorizaciones
|--------------------------------------------------------------------------
|
| Incluye el estado de EmailDelivery únicamente cuando el memorando generó una notificación de autorización.
|
*/

if ($delivery !== null) {
    $response['email'] = [
        'sent' =>
            false,

        'queued' =>
            $delivery->estaPendiente(),

        'status' =>
            $delivery->status,

        'delivery_id' =>
            $delivery->id,
    ];

    $response['message'] =
        $delivery->estaPendiente()
            ? 'El documento fue generado correctamente. La notificación para el proceso de firma se está enviando en segundo plano.'
            : 'El documento fue generado correctamente, pero no fue posible colocar la notificación en la cola de correo.';
}

return response()->json(
    $response
);

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
    |
    | Centraliza el almacenamiento de estructuras específicas según el formulario asociado al tipo de memorando.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Guardar datos específicos
    |--------------------------------------------------------------------------
    |
    | Aplica el almacenamiento complementario correspondiente al formulario
    | asociado con el tipo de memorando.
    |
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
            |
            | Guarda los datos complementarios de compras, servicios, repuestos o accesorios cuando el formulario los requiere.
            |
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
|
| Almacena los equipos en datos_extra y registra cada elemento como artículo del memorando.
|
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
            |
            | Guarda todos los campos dinámicos del formulario dentro de datos_extra para posteriores previews, PDF y consultas.
            |
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
    |
    | Incrementa de forma bloqueada el contador del prefijo y construye el folio con consecutivo, mes y año.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Construir folio consecutivo
    |--------------------------------------------------------------------------
    |
    | Bloquea el contador correspondiente, incrementa el valor y devuelve el
    | folio institucional con prefijo, consecutivo, mes y año.
    |
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
| Mis pases
|--------------------------------------------------------------------------
|
| Construye el historial del usuario para pases menores y mayores a 24 horas con filtros por período y tipo.
|
*/

/*
|--------------------------------------------------------------------------
| Mostrar historial de pases
|--------------------------------------------------------------------------
|
| Valida filtros, calcula métricas del período y construye el listado paginado
| del usuario autenticado.
|
*/

public function misPases(Request $request)
{
    $validated = $request->validate([
        'mes' => [
            'nullable',
            'integer',
            'between:1,12',
        ],

        'anio' => [
            'nullable',
            'integer',
            'between:2020,'.now()->year,
        ],

        'tipo' => [
            'nullable',
            'string',
            'in:todos,pase_temporal,autorizacion',
        ],
    ]);


    $mes = (int) (
        $validated['mes']
        ?? now()->month
    );

    $anio = (int) (
        $validated['anio']
        ?? now()->year
    );

    $tipoSeleccionado = $validated['tipo']
        ?? 'todos';

    $usuarioId = (int) auth()->id();


    /*
    |--------------------------------------------------------------------------
    | Consulta base del usuario
    |--------------------------------------------------------------------------
    |
    | Limita la consulta a memorandos del solicitante autenticado y a tipos de pase soportados.
    |
    */

    $consultaBase = Memorando::query()
        ->where(
            'solicitante_id',
            $usuarioId
        )
        ->whereHas(
            'tipo',
            function ($query) {
                $query->whereIn(
                    'slug',
                    [
                        'pase_temporal',
                        'autorizacion',
                    ]
                );
            }
        );


    /*
    |--------------------------------------------------------------------------
    | Años disponibles
    |--------------------------------------------------------------------------
    |
    | Obtiene los años con registros disponibles y conserva siempre el año actual.
    |
    */

    $aniosDisponibles = (clone $consultaBase)
        ->whereNotNull(
            'created_at'
        )
        ->selectRaw(
            'EXTRACT(YEAR FROM created_at)::int AS anio'
        )
        ->distinct()
        ->orderByDesc('anio')
        ->pluck('anio')
        ->map(
            static fn ($valor): int =>
                (int) $valor
        )
        ->push(
            now()->year
        )
        ->unique()
        ->sortDesc()
        ->values();


    /*
    |--------------------------------------------------------------------------
    | Consulta del periodo seleccionado
    |--------------------------------------------------------------------------
    |
    | Filtra los pases por mes y año antes de calcular métricas o aplicar el tipo seleccionado.
    |
    */

    $consultaPeriodo = (clone $consultaBase)
        ->whereMonth(
            'created_at',
            $mes
        )
        ->whereYear(
            'created_at',
            $anio
        );


    /*
    |--------------------------------------------------------------------------
    | Resumen del periodo
    |--------------------------------------------------------------------------
    |
    | Estos valores se calculan antes de paginar y no cambian al seleccionar
    | un tipo. De esta manera muestran el resumen completo del mes.
    |
    */

    $totalPases = (clone $consultaPeriodo)
        ->count();

    $pasesMenores = (clone $consultaPeriodo)
        ->whereHas(
            'tipo',
            fn ($query) => $query->where(
                'slug',
                'pase_temporal'
            )
        )
        ->count();

    $pasesMayores = (clone $consultaPeriodo)
        ->whereHas(
            'tipo',
            fn ($query) => $query->where(
                'slug',
                'autorizacion'
            )
        )
        ->count();


    /*
    |--------------------------------------------------------------------------
    | Listado filtrado y paginado
    |--------------------------------------------------------------------------
    |
    | Carga las relaciones necesarias y aplica el filtro de tipo antes de paginar el historial.
    |
    */

    $memorandos = (clone $consultaPeriodo)
        ->with([
            'tipo',
            'solicitante',
        ])
        ->when(
            $tipoSeleccionado !== 'todos',
            function ($query) use ($tipoSeleccionado) {
                $query->whereHas(
                    'tipo',
                    function ($tipoQuery) use ($tipoSeleccionado) {
                        $tipoQuery->where(
                            'slug',
                            $tipoSeleccionado
                        );
                    }
                );
            }
        )
        ->latest('created_at')
        ->paginate(10)
        ->withQueryString();


    return view(
        'memorandos.mis-pases',
        compact(
            'memorandos',
            'mes',
            'anio',
            'tipoSeleccionado',
            'aniosDisponibles',
            'totalPases',
            'pasesMenores',
            'pasesMayores'
        )
    );
}

/*
|--------------------------------------------------------------------------
| Detalle centralizado de un pase
|--------------------------------------------------------------------------
|
| Carga la información completa del pase y valida tipo y propiedad antes de mostrar el detalle.
|
*/

/*
|--------------------------------------------------------------------------
| Mostrar detalle de pase
|--------------------------------------------------------------------------
|
| Carga las relaciones principales y valida que el memorando sea un pase y que
| el usuario tenga permiso para consultarlo.
|
*/

public function showPase(
    Memorando $memorando
)
{
    $memorando->load([
        'tipo',
        'solicitante',
        'archivos',
        'historial',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Verificar que sea un pase
    |--------------------------------------------------------------------------
    |
    | Confirma que el memorando corresponda a pase temporal o autorización.
    |
    */

    abort_unless(
        in_array(
            $memorando->tipo?->slug,
            [
                'pase_temporal',
                'autorizacion',
            ],
            true
        ),
        404
    );


    /*
    |--------------------------------------------------------------------------
    | Verificar propietario
    |--------------------------------------------------------------------------
    |
    | Restringe la consulta al solicitante original o a un administrador.
    |
    */

    $esAdministrador =
        auth()->user()?->rol?->nombre
        ===
        'Administrador';


    abort_unless(
        (int) $memorando->solicitante_id
            ===
        (int) auth()->id()
        ||
        $esAdministrador,
        403,
        'No tienes permiso para consultar este pase.'
    );


    return view(
        'memorandos.show-pase',
        compact('memorando')
    );
}


    /*
    |--------------------------------------------------------------------------
    | Administración de pases
    |--------------------------------------------------------------------------
    |
    | Prepara el panel administrativo con filtros, búsqueda, período, tipo y resumen de estados.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar administración de pases
    |--------------------------------------------------------------------------
    |
    | Valida filtros y prepara el listado administrativo con búsqueda, métricas
    | y estados del período seleccionado.
    |
    */

    public function administracionPases(
        Request $request
    ): View {
        $validated = $request->validate([
            'mes' => [
                'nullable',
                'integer',
                'between:1,12',
            ],

            'anio' => [
                'nullable',
                'integer',
                'between:2020,'.now()->year,
            ],

            'buscar' => [
                'nullable',
                'string',
                'max:200',
            ],

            'estado' => [
                'nullable',
                Rule::in(
                    Memorando::ESTADOS_ADMINISTRATIVOS
                ),
            ],

            'tipo' => [
                'nullable',
                Rule::in([
                    'pase_temporal',
                    'autorizacion',
                ]),
            ],
        ]);

        $mes = (int) (
            $validated['mes']
            ?? now()->month
        );

        $anio = (int) (
            $validated['anio']
            ?? now()->year
        );

        $busqueda = trim(
            (string) (
                $validated['buscar']
                ?? ''
            )
        );

        $estadoSeleccionado =
            $validated['estado']
            ?? null;

        $tipoSeleccionado =
            $validated['tipo']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Consulta base
        |--------------------------------------------------------------------------
        |
        | El módulo administrativo únicamente incluye:
        |
        | - Pases menores a 24 horas.
        | - Pases mayores a 24 horas.
        |--------------------------------------------------------------------------
        */

        $consultaBase = Memorando::query()
            ->whereHas(
                'tipo',
                function ($query) {
                    $query->whereIn(
                        'slug',
                        [
                            'pase_temporal',
                            'autorizacion',
                        ]
                    );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Años disponibles
        |--------------------------------------------------------------------------
        |
        | Solo se muestran años que tienen pases registrados. El año actual se
        | conserva para que el periodo predeterminado siempre esté disponible.
        |
        */

        $aniosDisponibles = (clone $consultaBase)
            ->whereNotNull(
                'created_at'
            )
            ->selectRaw(
                'EXTRACT(YEAR FROM created_at)::int AS anio'
            )
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio')
            ->map(
                static fn ($valor): int =>
                    (int) $valor
            )
            ->push(
                now()->year
            )
            ->unique()
            ->sortDesc()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Consulta del periodo seleccionado
        |--------------------------------------------------------------------------
        */

        $consultaPeriodo = (clone $consultaBase)
            ->whereMonth(
                'created_at',
                $mes
            )
            ->whereYear(
                'created_at',
                $anio
            );

        $memorandos = (clone $consultaPeriodo)
            ->with([
                'tipo',
                'solicitante',
                'archivos',
            ])
            ->when(
                $busqueda !== '',
                function ($query) use ($busqueda) {

                    $termino = mb_strtolower(
                        trim($busqueda)
                    );

                    $codigoNormalizado = mb_strtoupper(
                        preg_replace(
                            '/\s+/',
                            '',
                            $busqueda
                        )
                    );

                    $idBuscado = null;

                    if (
                        preg_match(
                            '/^PASE-?0*(\d+)$/',
                            $codigoNormalizado,
                            $coincidencias
                        )
                    ) {
                        $idBuscado =
                            (int) $coincidencias[1];
                    } elseif (
                        ctype_digit($codigoNormalizado)
                    ) {
                        $idBuscado =
                            (int) $codigoNormalizado;
                    }

                    $query->where(
                        function ($subquery) use (
                            $termino,
                            $idBuscado
                        ) {
                            $subquery
                                ->whereRaw(
                                    "LOWER(COALESCE(codigo, '')) LIKE ?",
                                    ["%{$termino}%"]
                                )
                                ->orWhereRaw(
                                    'LOWER(asunto) LIKE ?',
                                    ["%{$termino}%"]
                                )
                                ->orWhereRaw(
                                    'LOWER(de_nombre) LIKE ?',
                                    ["%{$termino}%"]
                                )
                                ->orWhereHas(
                                    'solicitante',
                                    function ($usuarioQuery) use ($termino) {
                                        $usuarioQuery
                                            ->whereRaw(
                                                'LOWER(nombre) LIKE ?',
                                                ["%{$termino}%"]
                                            )
                                            ->orWhereRaw(
                                                'LOWER(correo) LIKE ?',
                                                ["%{$termino}%"]
                                            );
                                    }
                                )
                                ->when(
                                    $idBuscado !== null,
                                    fn ($codigoQuery) =>
                                        $codigoQuery->orWhere(
                                            'id',
                                            $idBuscado
                                        )
                                );
                        }
                    );
                }
            )
            ->when(
                filled($estadoSeleccionado),
                fn ($query) => $query->where(
                    'estado',
                    $estadoSeleccionado
                )
            )
            ->when(
                filled($tipoSeleccionado),
                fn ($query) => $query->whereHas(
                    'tipo',
                    fn ($tipoQuery) => $tipoQuery->where(
                        'slug',
                        $tipoSeleccionado
                    )
                )
            )
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $resumen = [
            'total' =>
                (clone $consultaPeriodo)
                    ->count(),

            'generados' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Memorando::ESTADO_GENERADO
                    )
                    ->count(),

            'aprobados' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Memorando::ESTADO_APROBADO
                    )
                    ->count(),

            'rechazados' =>
                (clone $consultaPeriodo)
                    ->where(
                        'estado',
                        Memorando::ESTADO_RECHAZADO
                    )
                    ->count(),
        ];

        return view(
            'administracion.pases.index',
            compact(
                'memorandos',
                'resumen',
                'busqueda',
                'estadoSeleccionado',
                'tipoSeleccionado',
                'mes',
                'anio',
                'aniosDisponibles'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Detalle administrativo del pase
    |--------------------------------------------------------------------------
    |
    | Carga el pase con relaciones e historial ordenado para su revisión administrativa.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar detalle administrativo
    |--------------------------------------------------------------------------
    |
    | Verifica el tipo de memorando y carga relaciones e historial para su
    | revisión desde el panel administrativo.
    |
    */

    public function showAdministracionPase(
        Memorando $memorando
    ): View {
        $this->asegurarQueEsPase(
            $memorando
        );

        $memorando->load([
            'tipo',
            'solicitante',
            'archivos',
            'historial' => fn ($query) =>
                $query->latest('created_at'),
        ]);

        return view(
            'administracion.pases.show',
            compact('memorando')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Aprobar pase
    |--------------------------------------------------------------------------
    |
    | Cambia un pase generado a aprobado, registra el historial y notifica al solicitante.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Aprobar pase pendiente
    |--------------------------------------------------------------------------
    |
    | Cambia el estado dentro de una transacción, registra el historial y
    | notifica posteriormente al solicitante.
    |
    */

    public function aprobarPase(
        Memorando $memorando
    ): RedirectResponse {
        $this->asegurarQueEsPase(
            $memorando
        );

        if (! $memorando->estaGenerado()) {
            return back()->withErrors([
                'memorando' =>
                    'Solamente se pueden aprobar pases pendientes de revisión.',
            ]);
        }

        DB::transaction(
            function () use ($memorando) {
                $estadoAnterior =
                    $memorando->estado;

                $memorando->update([
                    'estado' =>
                        Memorando::ESTADO_APROBADO,
                ]);

                MemorandoHistorial::create([
                    'memorando_id' =>
                        $memorando->id,

                    'usuario_id' =>
                        auth()->id(),

                    'estado_anterior' =>
                        $estadoAnterior,

                    'estado_nuevo' =>
                        Memorando::ESTADO_APROBADO,

                    'comentario' =>
                        'Pase aprobado desde el panel administrativo.',
                ]);
            }
        );

        $this->notificarEstadoPase(
            $memorando
        );

        return back()->with(
            'success',
            'El pase fue aprobado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Rechazar pase
    |--------------------------------------------------------------------------
    |
    | Valida el motivo, cambia el estado a rechazado, registra el historial y notifica al solicitante.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Rechazar pase pendiente
    |--------------------------------------------------------------------------
    |
    | Valida el motivo, cambia el estado, registra el historial y notifica al
    | solicitante sobre la resolución.
    |
    */

    public function rechazarPase(
    Request $request,
    Memorando $memorando
): RedirectResponse {

    $this->asegurarQueEsPase(
        $memorando
    );


    if (! $memorando->estaGenerado()) {

        return back()->withErrors([
            'memorando' =>
                'Solamente se pueden rechazar pases pendientes de revisión.',
        ]);

    }


    $validated = $request->validate(
        [
            'comentario' => [
                'required',
                'string',
                'max:500',
            ],
        ],
        [
            'comentario.required' =>
                'Debes indicar el motivo del rechazo.',

            'comentario.max' =>
                'El motivo del rechazo no puede superar los 500 caracteres.',
        ]
    );


    $comentario = trim(
        $validated['comentario']
    );


    DB::transaction(
        function () use (
            $memorando,
            $comentario
        ) {

            $estadoAnterior =
                $memorando->estado;


            $memorando->update([
                'estado' =>
                    Memorando::ESTADO_RECHAZADO,
            ]);


            MemorandoHistorial::create([
                'memorando_id' =>
                    $memorando->id,

                'usuario_id' =>
                    auth()->id(),

                'estado_anterior' =>
                    $estadoAnterior,

                'estado_nuevo' =>
                    Memorando::ESTADO_RECHAZADO,

                'comentario' =>
                    $comentario,
            ]);

        }
    );


    $this->notificarEstadoPase(
        $memorando
    );


    return redirect()
        ->route(
            'admin.pases.show',
            $memorando
        )
        ->with(
            'success',
            'El pase fue rechazado correctamente.'
        );
}


    /*
|--------------------------------------------------------------------------
| Notificar nuevo pase al equipo administrativo
|--------------------------------------------------------------------------
|
| La notificación se envía a administradores y usuarios TI activos.
|
*/

/*
|--------------------------------------------------------------------------
| Enviar notificación de nuevo pase
|--------------------------------------------------------------------------
|
| Distribuye la notificación entre administradores y usuarios TI activos,
| registrando cualquier fallo sin interrumpir el flujo principal.
|
*/

private function notificarNuevoPase(
    Memorando $memorando
): void {
    try {
        $memorando->loadMissing([
            'tipo',
            'solicitante',
        ]);

        $equipoAdministrativo = Usuario::query()
            ->where(
                'activo',
                true
            )
            ->whereHas(
                'rol',
                function ($query) {
                    $query->whereIn(
                        'nombre',
                        [
                            'Administrador',
                            'UsuarioTI',
                        ]
                    );
                }
            )
            ->get();

        if ($equipoAdministrativo->isEmpty()) {
            Log::warning(
                'No existen administradores o usuarios TI activos para recibir la notificación del nuevo pase.',
                [
                    'memorando_id' =>
                        $memorando->id,

                    'tipo' =>
                        $memorando->tipo?->slug,

                    'solicitante_id' =>
                        $memorando->solicitante_id,
                ]
            );

            return;
        }

        Notification::send(
            $equipoAdministrativo,
            new NuevoPaseNotification(
                $memorando
            )
        );
    } catch (\Throwable $exception) {
        Log::error(
            'No se pudo enviar la notificación del nuevo pase al equipo administrativo.',
            [
                'memorando_id' =>
                    $memorando->id,

                'tipo' =>
                    $memorando->tipo?->slug,

                'solicitante_id' =>
                    $memorando->solicitante_id,

                'error' =>
                    $exception->getMessage(),
            ]
        );
    }
}


    /*
    |--------------------------------------------------------------------------
    | Notificar actualización del pase
    |--------------------------------------------------------------------------
    |
    | Notifica al solicitante cuando cambia el estado administrativo de su pase.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Notificar cambio de estado del pase
    |--------------------------------------------------------------------------
    |
    | Envía al solicitante la actualización administrativa y registra cualquier
    | error de notificación.
    |
    */

    private function notificarEstadoPase(
        Memorando $memorando
    ): void {
        try {
            $memorando->loadMissing(
                'solicitante'
            );

            $memorando->solicitante?->notify(
                new EstadoPaseActualizadoNotification(
                    $memorando
                )
            );
        } catch (\Throwable $exception) {
            Log::error(
                'No se pudo registrar la notificación del cambio de estado del pase.',
                [
                    'memorando_id' =>
                        $memorando->id,

                    'solicitante_id' =>
                        $memorando->solicitante_id,

                    'estado' =>
                        $memorando->estado,

                    'error' =>
                        $exception->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Verificar que el memorando sea un pase
    |--------------------------------------------------------------------------
    |
    | Protege las acciones administrativas asegurando que el registro corresponda a un tipo de pase válido.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Validar tipo de pase
    |--------------------------------------------------------------------------
    |
    | Carga el tipo relacionado y bloquea cualquier registro que no corresponda
    | a pase temporal o autorización.
    |
    */

    private function asegurarQueEsPase(
        Memorando $memorando
    ): void {
        $memorando->loadMissing(
            'tipo'
        );

        abort_unless(
            in_array(
                $memorando->tipo?->slug,
                [
                    'pase_temporal',
                    'autorizacion',
                ],
                true
            ),
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Histórico de memorandos
    |--------------------------------------------------------------------------
    |
    | Mantiene compatibilidad con el historial anterior redirigiendo al historial centralizado de pases.
    |
    */

    /*
|--------------------------------------------------------------------------
| Histórico anterior
|--------------------------------------------------------------------------
|
| Se conserva para que los enlaces antiguos continúen funcionando.
| El historial de pases ahora está centralizado en misPases().
|
*/

/*
|--------------------------------------------------------------------------
| Redirigir historial anterior
|--------------------------------------------------------------------------
|
| Mantiene compatibilidad con rutas antiguas enviando al historial centralizado
| de pases.
|
*/

public function historico()
{
    return redirect()
        ->route('memorandos.mis-pases');
}





    /*
    |--------------------------------------------------------------------------
    | Descargar PDF
    |--------------------------------------------------------------------------
    |
    | Valida la existencia del archivo generado y fuerza su descarga desde el almacenamiento.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Descargar documento generado
    |--------------------------------------------------------------------------
    |
    | Verifica la existencia física del PDF antes de entregarlo como descarga.
    |
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
    |
    | Valida la existencia del archivo generado y lo devuelve para visualización directa en el navegador.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Visualizar documento generado
    |--------------------------------------------------------------------------
    |
    | Verifica la existencia del PDF y lo devuelve directamente al navegador.
    |
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