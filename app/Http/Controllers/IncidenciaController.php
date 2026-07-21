<?php

namespace App\Http\Controllers;

use App\Mail\IncidenciaMail;
use App\Models\Incidencia;
use App\Models\IncidenciaArchivo;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        OcrService $ocr
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

        try {
            Mail::to(
                'alejandrotsc01@gmail.com'
            )->send(
                new IncidenciaMail(
                    $incidencia,
                    $textoOCR
                )
            );

            $incidencia->update([
                'correo_enviado' =>
                    true,

                'fecha_envio_correo' =>
                    now(),
            ]);

            return response()->json([
                'success' =>
                    true,

                'codigo' =>
                    $incidencia->codigo,

                'message' =>
                    'El reporte de incidencia fue enviado correctamente y el equipo TI fue notificado.',
            ]);

        } catch (\Throwable $e) {
            Log::error(
                'Error enviando incidencia '
                .$incidencia->codigo,
                [
                    'error' =>
                        $e->getMessage(),
                ]
            );

            $incidencia->update([
                'correo_enviado' =>
                    false,
            ]);

            return response()->json([
                'success' =>
                    false,

                'codigo' =>
                    $incidencia->codigo,

                'message' =>
                    'El reporte de incidencia fue registrado, pero no fue posible enviar la notificación al equipo TI.',
            ], 500);
        }
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