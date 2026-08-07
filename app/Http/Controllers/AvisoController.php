<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Models\Usuario;
use App\Notifications\NuevoAvisoTiNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| Controlador de avisos TI
|--------------------------------------------------------------------------
|
| Gestiona la publicación, consulta, edición y estado de los avisos del
| Portal TI, incluyendo filtros administrativos y notificaciones a usuarios
| cuando un aviso comienza a estar visible.
|
*/

class AvisoController extends Controller
{


    /*
|--------------------------------------------------------------------------
| Avisos públicos vigentes
|--------------------------------------------------------------------------
|
| Obtiene los avisos activos cuya ventana de publicación se encuentra vigente y los muestra paginados en la vista pública.
|
*/

/*
|--------------------------------------------------------------------------
| Consultar avisos públicos
|--------------------------------------------------------------------------
|
| Recupera únicamente los avisos disponibles para los usuarios según su
| estado activo y rango de vigencia.
|
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
    |
    | Construye el listado administrativo de avisos aplicando búsqueda, filtros por estado, paginación y resumen de indicadores.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Consultar listado administrativo
    |--------------------------------------------------------------------------
    |
    | Aplica búsqueda textual, filtros de estado y paginación, además de
    | preparar los contadores utilizados por el panel administrativo.
    |
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
    |
    | Presenta el formulario utilizado para registrar un nuevo aviso TI.
    |
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
    |
    | Normaliza y valida la solicitud, crea el aviso y notifica a los usuarios cuando el contenido queda visible inmediatamente.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Crear aviso
    |--------------------------------------------------------------------------
    |
    | Procesa los datos recibidos, registra el aviso y dispara notificaciones
    | cuando el contenido debe mostrarse desde ese momento.
    |
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


        $aviso = Aviso::create([
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


        if ($this->estaVisibleAhora($aviso)) {
            $this->notificarNuevoAviso(
                $aviso
            );
        }


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
    |
    | Presenta el formulario de edición con la información del aviso seleccionado.
    |
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
    |
    | Actualiza los datos del aviso y notifica a los usuarios únicamente cuando pasa de no visible a visible.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Modificar aviso
    |--------------------------------------------------------------------------
    |
    | Actualiza el registro existente y evita notificaciones duplicadas cuando
    | el aviso ya se encontraba visible antes de la edición.
    |
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


        $estabaVisible =
            $this->estaVisibleAhora(
                $aviso
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


        $aviso->refresh();

        if (
            ! $estabaVisible
            && $this->estaVisibleAhora($aviso)
        ) {
            $this->notificarNuevoAviso(
                $aviso
            );
        }


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
    |
    | Invierte el estado activo del aviso y genera una notificación cuando la activación lo vuelve visible.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cambiar estado del aviso
    |--------------------------------------------------------------------------
    |
    | Activa o desactiva el registro y notifica únicamente cuando el cambio lo
    | convierte en un aviso visible para los usuarios.
    |
    */

    public function changeStatus(
        Aviso $aviso
    ): RedirectResponse {
        $estabaVisible =
            $this->estaVisibleAhora(
                $aviso
            );

        $aviso->update([
            'activo' => ! $aviso->activo,
        ]);

        $aviso->refresh();

        if (
            ! $estabaVisible
            && $this->estaVisibleAhora($aviso)
        ) {
            $this->notificarNuevoAviso(
                $aviso
            );
        }


        return back()->with(
            'success',
            $aviso->activo
                ? 'Aviso activado correctamente.'
                : 'Aviso desactivado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verificar si el aviso está visible
    |--------------------------------------------------------------------------
    |
    | Determina si un aviso está activo y dentro de su rango válido de publicación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Evaluar visibilidad actual
    |--------------------------------------------------------------------------
    |
    | Comprueba estado activo, fecha de inicio y fecha de finalización para
    | determinar si el aviso debe mostrarse en este momento.
    |
    */

    private function estaVisibleAhora(
        Aviso $aviso
    ): bool {
        if (! $aviso->activo) {
            return false;
        }

        $ahora = now();

        if (
            $aviso->fecha_inicio
            && $aviso->fecha_inicio->isFuture()
        ) {
            return false;
        }

        if (
            $aviso->fecha_fin
            && $aviso->fecha_fin->isPast()
        ) {
            return false;
        }

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Notificar aviso visible
    |--------------------------------------------------------------------------
    |
    | Envía la notificación del nuevo aviso a los usuarios activos, excluyendo al usuario que realizó la publicación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enviar notificación del aviso
    |--------------------------------------------------------------------------
    |
    | Selecciona usuarios activos distintos del creador y distribuye la
    | notificación correspondiente mediante el sistema de notificaciones.
    |
    */

    private function notificarNuevoAviso(
        Aviso $aviso
    ): void {
        try {
            $usuarios = Usuario::query()
                ->where(
                    'activo',
                    true
                )
                ->whereKeyNot(
                    Auth::id()
                )
                ->get();

            if ($usuarios->isEmpty()) {
                return;
            }

            Notification::send(
                $usuarios,
                new NuevoAvisoTiNotification(
                    $aviso
                )
            );
        } catch (\Throwable $exception) {
            Log::error(
                'No se pudieron registrar las notificaciones del aviso TI.',
                [
                    'aviso_id' =>
                        $aviso->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar información
    |--------------------------------------------------------------------------
    |
    | Limpia espacios innecesarios en título y mensaje antes de ejecutar la validación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Normalizar datos de entrada
    |--------------------------------------------------------------------------
    |
    | Reduce espacios repetidos y limpia extremos antes de aplicar las reglas
    | de validación.
    |
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
    |
    | Define reglas y mensajes de validación para contenido, fechas y estado del aviso.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Validar información del aviso
    |--------------------------------------------------------------------------
    |
    | Aplica reglas dinámicas para las fechas y valida longitud, contenido y
    | estado antes de guardar los cambios.
    |
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