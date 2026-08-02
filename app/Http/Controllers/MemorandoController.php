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
| Enviar pase menor a 24 horas por correo
|--------------------------------------------------------------------------
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
                'alejandrotsc01@gmail.com',

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
            'alejandrotsc01@gmail.com',

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
| Mis pases
|--------------------------------------------------------------------------
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
    */

    public function administracionPases(
        Request $request
    ): View {
        $validated = $request->validate([
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

        $memorandos = (clone $consultaBase)
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
                (clone $consultaBase)
                    ->count(),

            'generados' =>
                (clone $consultaBase)
                    ->where(
                        'estado',
                        Memorando::ESTADO_GENERADO
                    )
                    ->count(),

            'aprobados' =>
                (clone $consultaBase)
                    ->where(
                        'estado',
                        Memorando::ESTADO_APROBADO
                    )
                    ->count(),

            'rechazados' =>
                (clone $consultaBase)
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
                'tipoSeleccionado'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Detalle administrativo del pase
    |--------------------------------------------------------------------------
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
    | Notificar nuevo pase a los administradores
    |--------------------------------------------------------------------------
    */

    private function notificarNuevoPase(
        Memorando $memorando
    ): void {
        try {
            $memorando->loadMissing([
                'tipo',
                'solicitante',
            ]);

            $administradores =
                Usuario::query()
                    ->where(
                        'activo',
                        true
                    )
                    ->whereHas(
                        'rol',
                        function ($query) {
                            $query->where(
                                'nombre',
                                'Administrador'
                            );
                        }
                    )
                    ->get();

            if ($administradores->isEmpty()) {
                Log::warning(
                    'No existen administradores activos para recibir la notificación del nuevo pase.',
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
                $administradores,
                new NuevoPaseNotification(
                    $memorando
                )
            );
        } catch (\Throwable $exception) {
            Log::error(
                'No se pudo enviar la notificación del nuevo pase a los administradores.',
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

public function historico()
{
    return redirect()
        ->route('memorandos.mis-pases');
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