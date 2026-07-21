<?php

namespace App\Http\Controllers;

use App\Mail\IncidenciaMail;
use App\Models\Incidencia;
use App\Models\IncidenciaArchivo;
use App\Services\Mail\TrackedMailService;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidenciaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listado general
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $incidencias =
            Incidencia::query()
                ->with([
                    'usuario',
                    'archivos',
                ])
                ->latest()
                ->get();

        return view(
            'incidencias.index',
            compact('incidencias')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Formulario de incidencia
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'incidencias.create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar incidencia
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        OcrService $ocr,
        TrackedMailService $trackedMail
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'titulo' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'required',
                'string',
            ],

            'tiempo_problema' => [
                'nullable',
                'string',
            ],

            'afectacion' => [
                'nullable',
                'string',
            ],

            'equipo' => [
                'nullable',
                'string',
            ],

            'ubicacion' => [
                'nullable',
                'string',
            ],

            'archivos' => [
                'nullable',
                'array',
            ],

            'archivos.*' => [
                'image',
                'max:10240',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generar código
        |--------------------------------------------------------------------------
        */

        $ultima =
            Incidencia::query()
                ->orderByDesc('id')
                ->first();

        $numero = $ultima
            ? intval(
                substr(
                    $ultima->codigo,
                    4
                )
            ) + 1
            : 1;

        $codigo =
            'INC-'
            .str_pad(
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

        $incidencia =
            Incidencia::create([
                'codigo' =>
                    $codigo,

                'usuario_id' =>
                    Auth::id(),

                'titulo' =>
                    $validated['titulo'],

                'descripcion' =>
                    $validated['descripcion'],

                'tiempo_problema' =>
                    $validated['tiempo_problema']
                    ?? null,

                'afectacion' =>
                    $validated['afectacion']
                    ?? null,

                'equipo' =>
                    $validated['equipo']
                    ?? null,

                'ubicacion' =>
                    $validated['ubicacion']
                    ?? null,

                'estado' =>
                    'Abierta',

                'prioridad' =>
                    'Media',

                'correo_enviado' =>
                    false,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Guardar archivos y ejecutar OCR
        |--------------------------------------------------------------------------
        */

        $textoOCR = [];

        if ($request->hasFile('archivos')) {
            foreach (
                $request->file('archivos')
                as $archivo
            ) {
                /*
                 * Guardar imagen.
                 */
                $ruta = $archivo->store(
                    'incidencias',
                    'public'
                );

                /*
                 * Ejecutar OCR.
                 */
                $texto = $ocr->leerImagen(
                    storage_path(
                        'app/public/'.$ruta
                    )
                );

                if (
                    is_string($texto)
                    && trim($texto) !== ''
                ) {
                    $textoOCR[] = $texto;
                }

                /*
                 * Guardar registro del archivo.
                 */
                IncidenciaArchivo::create([
                    'incidencia_id' =>
                        $incidencia->id,

                    'usuario_id' =>
                        Auth::id(),

                    'nombre_original' =>
                        $archivo->getClientOriginalName(),

                    'nombre_archivo' =>
                        basename($ruta),

                    'ruta' =>
                        $ruta,

                    'extension' =>
                        $archivo->getClientOriginalExtension(),

                    'tamano' =>
                        $archivo->getSize(),

                    'texto_ocr' =>
                        $texto ?: null,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Enviar correo
        |--------------------------------------------------------------------------
        */

        /*
|--------------------------------------------------------------------------
| Enviar correo con seguimiento
|--------------------------------------------------------------------------
*/

$delivery = $trackedMail->send(
    emailable: $incidencia,

    mailable: new IncidenciaMail(
        $incidencia,
        $textoOCR
    ),

    recipientEmail:
        'alejandrotsc01@gmail.com',

    mailType:
        'incidencia_creada',

    recipientName:
        'Equipo de soporte TI',

    subject:
        'Nueva incidencia '.$incidencia->codigo,

    metadata: [
        'codigo' =>
            $incidencia->codigo,

        'usuario_id' =>
            Auth::id(),

        'cantidad_archivos' =>
            $incidencia->archivos()->count(),

        'cantidad_textos_ocr' =>
            count($textoOCR),
    ]
);

$emailSent =
    $delivery->fueEnviado();

/*
|--------------------------------------------------------------------------
| Actualizar compatibilidad con la tabla incidencias
|--------------------------------------------------------------------------
*/

$incidencia->update([
    'correo_enviado' =>
        $emailSent,

    'fecha_envio_correo' =>
        $emailSent
            ? $delivery->sent_at
            : null,
]);

/*
|--------------------------------------------------------------------------
| Respuesta
|--------------------------------------------------------------------------
|
| success siempre será true porque el reporte sí quedó registrado.
| email.sent indica si la notificación SMTP se envió.
|
*/

return response()->json([
    'success' =>
        true,

    'registered' =>
        true,

    'codigo' =>
        $incidencia->codigo,

    'email' => [
        'sent' =>
            $emailSent,

        'status' =>
            $delivery->status,

        'delivery_id' =>
            $delivery->id,
    ],

    'message' =>
        $emailSent
            ? 'El reporte de incidencia fue registrado correctamente y el equipo TI fue notificado.'
            : 'El reporte de incidencia fue registrado correctamente, pero no fue posible enviar la notificación por correo. El error quedó registrado para su revisión.',
]);
    }

    /*
    |--------------------------------------------------------------------------
    | Incidencias del usuario con filtro mensual
    |--------------------------------------------------------------------------
    */

    public function misIncidencias(
        Request $request
    ) {
        $validated = $request->validate([
            'mes' => [
                'nullable',
                'integer',
                'between:1,12',
            ],

            'anio' => [
                'nullable',
                'integer',
                'min:2020',
                'max:'.now()->year,
            ],
        ]);

        /*
         * Mostrar el mes actual cuando no se recibe ningún filtro.
         */
        $mes = (int) (
            $validated['mes']
            ?? now()->month
        );

        $anio = (int) (
            $validated['anio']
            ?? now()->year
        );

        $usuarioId =
            (int) Auth::id();

        /*
         * Meses disponibles para el filtro.
         */
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        /*
         * Obtener los años en los que el usuario tiene incidencias.
         *
         * EXTRACT se utiliza porque la base de datos es PostgreSQL.
         */
        $aniosDisponibles =
            Incidencia::query()
                ->where(
                    'usuario_id',
                    $usuarioId
                )
                ->selectRaw(
                    'DISTINCT EXTRACT(YEAR FROM created_at)::integer AS anio'
                )
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
         * Aplicar filtro de mes y año.
         */
        $incidencias =
            Incidencia::query()
                ->where(
                    'usuario_id',
                    $usuarioId
                )
                ->with([
                    'archivos',
                ])
                ->whereYear(
                    'created_at',
                    $anio
                )
                ->whereMonth(
                    'created_at',
                    $mes
                )
                ->latest()
                ->get();

        return view(
            'incidencias.mis-incidencias',
            compact(
                'incidencias',
                'mes',
                'anio',
                'meses',
                'aniosDisponibles'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Detalle de incidencia
    |--------------------------------------------------------------------------
    */

    public function show(
        Incidencia $incidencia
    ) {
        $incidencia->load([
            'usuario',
            'archivos',
        ]);

        return view(
            'incidencias.show',
            compact('incidencia')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cerrar incidencia
    |--------------------------------------------------------------------------
    */

    public function cerrar(
        Incidencia $incidencia
    ) {
        $incidencia->update([
            'estado' =>
                'Cerrada',
        ]);

        return back();
    }
}