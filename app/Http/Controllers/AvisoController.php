<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AvisoController extends Controller
{


    /*
|--------------------------------------------------------------------------
| Avisos públicos vigentes
|--------------------------------------------------------------------------
*/

public function publicos(): View
{
    $ahora = now();

    $avisos = Aviso::query()
        ->where(
            'activo',
            true
        )
        ->where(
            function ($query) use ($ahora) {
                $query
                    ->whereNull(
                        'fecha_inicio'
                    )
                    ->orWhere(
                        'fecha_inicio',
                        '<=',
                        $ahora
                    );
            }
        )
        ->where(
            function ($query) use ($ahora) {
                $query
                    ->whereNull(
                        'fecha_fin'
                    )
                    ->orWhere(
                        'fecha_fin',
                        '>=',
                        $ahora
                    );
            }
        )
        ->orderByDesc(
            'created_at'
        )
        ->paginate(10);

    return view(
        'avisos.publicos',
        compact('avisos')
    );
}

    /*
    |--------------------------------------------------------------------------
    | Listado
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {
        $busqueda = trim(
            (string) $request->input(
                'buscar',
                ''
            )
        );

        $estadoSeleccionado = $request->input(
            'estado'
        );

        $ahora = now();


        $avisos = Aviso::query()
            ->with('creador')

            ->when(
                $busqueda !== '',
                function ($query) use ($busqueda) {
                    $termino = mb_strtolower(
                        $busqueda
                    );

                    $query->where(
                        function ($subquery) use ($termino) {
                            $subquery
                                ->whereRaw(
                                    'LOWER(titulo) LIKE ?',
                                    ["%{$termino}%"]
                                )
                                ->orWhereRaw(
                                    'LOWER(mensaje) LIKE ?',
                                    ["%{$termino}%"]
                                );
                        }
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | Avisos visibles
            |--------------------------------------------------------------------------
            */

            ->when(
                $estadoSeleccionado === 'visible',
                function ($query) use ($ahora) {
                    $query
                        ->where(
                            'activo',
                            true
                        )
                        ->where(
                            function ($subquery) use ($ahora) {
                                $subquery
                                    ->whereNull(
                                        'fecha_inicio'
                                    )
                                    ->orWhere(
                                        'fecha_inicio',
                                        '<=',
                                        $ahora
                                    );
                            }
                        )
                        ->where(
                            function ($subquery) use ($ahora) {
                                $subquery
                                    ->whereNull(
                                        'fecha_fin'
                                    )
                                    ->orWhere(
                                        'fecha_fin',
                                        '>=',
                                        $ahora
                                    );
                            }
                        );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | Avisos programados
            |--------------------------------------------------------------------------
            */

            ->when(
                $estadoSeleccionado === 'programado',
                fn ($query) => $query
                    ->where(
                        'activo',
                        true
                    )
                    ->where(
                        'fecha_inicio',
                        '>',
                        $ahora
                    )
            )

            /*
            |--------------------------------------------------------------------------
            | Avisos finalizados
            |--------------------------------------------------------------------------
            */

            ->when(
                $estadoSeleccionado === 'finalizado',
                fn ($query) => $query
                    ->where(
                        'activo',
                        true
                    )
                    ->whereNotNull(
                        'fecha_fin'
                    )
                    ->where(
                        'fecha_fin',
                        '<',
                        $ahora
                    )
            )

            /*
            |--------------------------------------------------------------------------
            | Avisos inactivos
            |--------------------------------------------------------------------------
            */

            ->when(
                $estadoSeleccionado === 'inactivo',
                fn ($query) => $query->where(
                    'activo',
                    false
                )
            )

            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Contadores
        |--------------------------------------------------------------------------
        */

        $resumen = [
            'total' => Aviso::count(),

            'visibles' => Aviso::query()
                ->where(
                    'activo',
                    true
                )
                ->where(
                    function ($query) use ($ahora) {
                        $query
                            ->whereNull(
                                'fecha_inicio'
                            )
                            ->orWhere(
                                'fecha_inicio',
                                '<=',
                                $ahora
                            );
                    }
                )
                ->where(
                    function ($query) use ($ahora) {
                        $query
                            ->whereNull(
                                'fecha_fin'
                            )
                            ->orWhere(
                                'fecha_fin',
                                '>=',
                                $ahora
                            );
                    }
                )
                ->count(),

            'programados' => Aviso::query()
                ->where(
                    'activo',
                    true
                )
                ->where(
                    'fecha_inicio',
                    '>',
                    $ahora
                )
                ->count(),

            'inactivos' => Aviso::query()
                ->where(
                    'activo',
                    false
                )
                ->count(),
        ];


        return view(
            'avisos.index',
            compact(
                'avisos',
                'resumen',
                'busqueda',
                'estadoSeleccionado'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'avisos.create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {
        $this->normalizarDatos(
            $request
        );

        $validated = $this->validarAviso(
            $request,
            false
        );


        Aviso::create([
            'titulo' => $validated['titulo'],

            'mensaje' => $validated['mensaje'],

            'fecha_inicio' =>
                $validated['fecha_inicio']
                ?? null,

            'fecha_fin' =>
                $validated['fecha_fin']
                ?? null,

            'activo' =>
                $request->boolean(
                    'activo'
                ),

            'creado_por' =>
                Auth::id(),
        ]);


        return redirect()
            ->route('avisos.index')
            ->with(
                'success',
                'Aviso creado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    public function edit(
        Aviso $aviso
    ): View {
        return view(
            'avisos.edit',
            compact('aviso')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Aviso $aviso
    ): RedirectResponse {
        $this->normalizarDatos(
            $request
        );

        $validated = $this->validarAviso(
            $request
        );


        $aviso->update([
            'titulo' => $validated['titulo'],

            'mensaje' => $validated['mensaje'],

            'fecha_inicio' =>
                $validated['fecha_inicio']
                ?? null,

            'fecha_fin' =>
                $validated['fecha_fin']
                ?? null,

            'activo' =>
                $request->boolean(
                    'activo'
                ),
        ]);


        return redirect()
            ->route('avisos.index')
            ->with(
                'success',
                'Aviso actualizado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Activar o desactivar
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        Aviso $aviso
    ): RedirectResponse {
        $aviso->update([
            'activo' => ! $aviso->activo,
        ]);


        return back()->with(
            'success',
            $aviso->activo
                ? 'Aviso activado correctamente.'
                : 'Aviso desactivado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar información
    |--------------------------------------------------------------------------
    */

    private function normalizarDatos(
        Request $request
    ): void {
        $request->merge([
            'titulo' => preg_replace(
                '/\s+/',
                ' ',
                trim(
                    (string) $request->input(
                        'titulo'
                    )
                )
            ),

            'mensaje' => preg_replace(
                '/[ \t]+/',
                ' ',
                trim(
                    (string) $request->input(
                        'mensaje'
                    )
                )
            ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

    private function validarAviso(
        Request $request
    ): array {
        $reglasFechaFin = [
            'nullable',
            'date',
        ];


        if (
            $request->filled(
                'fecha_inicio'
            )
        ) {
            $reglasFechaFin[] =
                'after:fecha_inicio';
        } else {
            $reglasFechaFin[] =
                'after:now';
        }


        return $request->validate([
            'titulo' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],

            'mensaje' => [
                'required',
                'string',
                'min:5',
                'max:500',
            ],

            'fecha_inicio' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'fecha_fin' =>
                $reglasFechaFin,

            'activo' => [
                'nullable',
                'boolean',
            ],
        ], [
            'titulo.required' =>
                'Debe ingresar el título del aviso.',

            'titulo.min' =>
                'El título debe tener al menos 3 caracteres.',

            'titulo.max' =>
                'El título no puede superar los 150 caracteres.',

            'mensaje.required' =>
                'Debe ingresar el mensaje del aviso.',

            'mensaje.min' =>
                'El mensaje debe tener al menos 5 caracteres.',

            'mensaje.max' =>
                'El mensaje no puede superar los 500 caracteres.',

            'fecha_inicio.date' =>
                'La fecha de inicio no es válida.',

            'fecha_inicio.after_or_equal' =>
                'La fecha de inicio no puede ser anterior al día actual.',

            'fecha_fin.date' =>
                'La fecha de finalización no es válida.',

            'fecha_fin.after' =>
                'La fecha de finalización debe ser posterior a la fecha de inicio.',

            'activo.boolean' =>
                'El estado seleccionado no es válido.',
        ]);
    }
}