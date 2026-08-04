<?php

namespace App\Http\Controllers;

use App\Models\GuardiaSoporte;
use App\Models\Usuario;
use App\Notifications\GuardiaAsignadaNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class GuardiaSoporteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Administración de guardias
    |--------------------------------------------------------------------------
    |
    | Vista disponible únicamente para el Administrador.
    |
    */

    public function index(
        Request $request
    ): View {
        $this->autorizarAdministrador();

        [
            $mes,
            $anio,
        ] = $this->obtenerPeriodo(
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Usuarios disponibles para asignación
        |--------------------------------------------------------------------------
        |
        | Solamente se muestran usuarios:
        |
        | - Activos.
        | - Con rol UsuarioTI.
        |
        */

        $agentes = Usuario::query()
            ->with('rol')
            ->where('activo', true)
            ->whereHas(
                'rol',
                function (Builder $query) {
                    $query->where(
                        'nombre',
                        'UsuarioTI'
                    );
                }
            )
            ->orderBy('nombre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Guardias del período seleccionado
        |--------------------------------------------------------------------------
        */

        $guardias = GuardiaSoporte::query()
            ->with([
                'agente.rol',
                'creador.rol',
            ])
            ->whereMonth(
                'fecha',
                $mes
            )
            ->whereYear(
                'fecha',
                $anio
            )
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->paginate(12)
            ->withQueryString();

        $aniosDisponibles =
            $this->obtenerAniosDisponibles();

        return view(
            'guardias.index',
            compact(
                'agentes',
                'guardias',
                'mes',
                'anio',
                'aniosDisponibles'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Guardias asignadas
    |--------------------------------------------------------------------------
    |
    | UsuarioTI:
    | Solo consulta sus propias guardias.
    |
    | Administrador:
    | Puede consultar el calendario general.
    |
    */

    public function misGuardias(
        Request $request
    ): View {
        $usuario = $request->user();

        [
            $mes,
            $anio,
        ] = $this->obtenerPeriodo(
            $request
        );

        $query = GuardiaSoporte::query()
            ->with([
                'agente.rol',
            ])
            ->activas()
            ->whereMonth(
                'fecha',
                $mes
            )
            ->whereYear(
                'fecha',
                $anio
            );

        /*
        |--------------------------------------------------------------------------
        | Restringir las guardias del UsuarioTI
        |--------------------------------------------------------------------------
        */

        if ($usuario->esUsuarioTI()) {
            $query->where(
                'usuario_id',
                $usuario->id
            );
        } elseif (! $usuario->esAdministrador()) {
            abort(
                403,
                'No tienes permiso para consultar las guardias.'
            );
        }

        $guardias = $query
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $aniosDisponibles =
            $this->obtenerAniosDisponibles();

        return view(
            'guardias.mis-guardias',
            compact(
                'guardias',
                'mes',
                'anio',
                'aniosDisponibles'
            )
        );
    }


/*
|--------------------------------------------------------------------------
| Crear guardia
|--------------------------------------------------------------------------
*/

public function store(
    Request $request
): RedirectResponse {
    $this->autorizarAdministrador();

    $validated = $this->validarGuardia(
        $request
    );

    /*
    |--------------------------------------------------------------------------
    | Verificar que el agente sea UsuarioTI
    |--------------------------------------------------------------------------
    */

    $this->obtenerAgenteValido(
        (int) $validated['usuario_id']
    );

    /*
    |--------------------------------------------------------------------------
    | Registrar guardia
    |--------------------------------------------------------------------------
    */

    $guardia = GuardiaSoporte::create([
        'usuario_id' =>
            $validated['usuario_id'],

        'creado_por' =>
            $request->user()->id,

        'fecha' =>
            $validated['fecha'],

        'hora_inicio' =>
            $validated['hora_inicio'],

        'hora_fin' =>
            $validated['hora_fin'],

        'ubicacion' =>
            $validated['ubicacion'],

        'observacion' =>
            $validated['observacion']
                ?? null,

        'activo' =>
            true,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Notificar al agente asignado
    |--------------------------------------------------------------------------
    */

    $this->notificarGuardiaAsignada(
        $guardia
    );

    return redirect()
        ->route(
            'admin.guardias.index',
            [
                'mes' => Carbon::parse(
                    $validated['fecha']
                )->month,

                'anio' => Carbon::parse(
                    $validated['fecha']
                )->year,
            ]
        )
        ->with(
            'success',
            'La guardia fue asignada correctamente.'
        );
}


    /*
|--------------------------------------------------------------------------
| Actualizar guardia
|--------------------------------------------------------------------------
*/

public function update(
    Request $request,
    GuardiaSoporte $guardia
): RedirectResponse {
    $this->autorizarAdministrador();

    $validated = $this->validarGuardia(
        $request,
        $guardia
    );

    /*
    |--------------------------------------------------------------------------
    | Verificar que el agente sea UsuarioTI
    |--------------------------------------------------------------------------
    */

    $this->obtenerAgenteValido(
        (int) $validated['usuario_id']
    );

    /*
    |--------------------------------------------------------------------------
    | Comprobar si la guardia fue reasignada
    |--------------------------------------------------------------------------
    */

    $agenteAnteriorId =
        (int) $guardia->usuario_id;

    $nuevoAgenteId =
        (int) $validated['usuario_id'];

    $fueReasignada =
        $agenteAnteriorId
        !== $nuevoAgenteId;

    /*
    |--------------------------------------------------------------------------
    | Actualizar información
    |--------------------------------------------------------------------------
    */

    $guardia->update([
        'usuario_id' =>
            $validated['usuario_id'],

        'fecha' =>
            $validated['fecha'],

        'hora_inicio' =>
            $validated['hora_inicio'],

        'hora_fin' =>
            $validated['hora_fin'],

        'ubicacion' =>
            $validated['ubicacion'],

        'observacion' =>
            $validated['observacion']
                ?? null,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Notificar únicamente cuando cambió el agente
    |--------------------------------------------------------------------------
    */

    if ($fueReasignada) {
        $guardia->refresh();

        $this->notificarGuardiaAsignada(
            $guardia
        );
    }

    return redirect()
        ->route(
            'admin.guardias.index',
            [
                'mes' => Carbon::parse(
                    $validated['fecha']
                )->month,

                'anio' => Carbon::parse(
                    $validated['fecha']
                )->year,
            ]
        )
        ->with(
            'success',
            $fueReasignada
                ? 'La guardia fue actualizada y el nuevo agente fue notificado.'
                : 'La guardia fue actualizada correctamente.'
        );
}


    /*
    |--------------------------------------------------------------------------
    | Activar o cancelar guardia
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        Request $request,
        GuardiaSoporte $guardia
    ): RedirectResponse {
        $this->autorizarAdministrador();

        /*
        |--------------------------------------------------------------------------
        | Evitar reactivar una guardia pasada
        |--------------------------------------------------------------------------
        */

        if (
            ! $guardia->activo
            && $guardia->fecha->isBefore(
                today()
            )
        ) {
            return back()
                ->withErrors([
                    'guardia' =>
                        'No se puede reactivar una guardia que ya pasó.',
                ]);
        }

        $guardia->update([
            'activo' =>
                ! $guardia->activo,
        ]);

        return back()->with(
            'success',
            $guardia->activo
                ? 'La guardia fue activada correctamente.'
                : 'La guardia fue cancelada correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validar datos de la guardia
    |--------------------------------------------------------------------------
    */

    private function validarGuardia(
        Request $request,
        ?GuardiaSoporte $guardia = null
    ): array {
        return $request->validate(
            [
                'usuario_id' => [
                    'required',
                    'integer',
                    'exists:usuarios,id',
                ],

                'fecha' => [
                    'required',
                    'date',
                    'after_or_equal:today',

                    Rule::unique(
                        'guardias_soporte',
                        'fecha'
                    )->ignore(
                        $guardia?->id
                    ),

                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ): void {
                        try {
                            $fecha = Carbon::parse(
                                $value
                            );
                        } catch (\Throwable) {
                            return;
                        }

                        if (! $fecha->isWeekend()) {
                            $fail(
                                'La fecha de la guardia debe ser sábado o domingo.'
                            );
                        }
                    },
                ],

                'hora_inicio' => [
                    'required',
                    'date_format:H:i',
                ],

                'hora_fin' => [
                    'required',
                    'date_format:H:i',
                    'after:hora_inicio',
                ],

                'ubicacion' => [
                    'required',
                    'string',

                    Rule::in([
                        GuardiaSoporte::UBICACION_TVC,
                        GuardiaSoporte::UBICACION_CNT,
                    ]),
                ],

                'observacion' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
            ],
            [
                'usuario_id.required' =>
                    'Debe seleccionar un agente de soporte.',

                'usuario_id.integer' =>
                    'El agente seleccionado no es válido.',

                'usuario_id.exists' =>
                    'El agente seleccionado no existe.',

                'fecha.required' =>
                    'Debe seleccionar la fecha de la guardia.',

                'fecha.date' =>
                    'La fecha seleccionada no es válida.',

                'fecha.after_or_equal' =>
                    'No puedes asignar una guardia en una fecha pasada.',

                'fecha.unique' =>
                    'Ya existe una guardia asignada para esta fecha.',

                'hora_inicio.required' =>
                    'Debe ingresar la hora de inicio.',

                'hora_inicio.date_format' =>
                    'La hora de inicio no tiene un formato válido.',

                'hora_fin.required' =>
                    'Debe ingresar la hora de finalización.',

                'hora_fin.date_format' =>
                    'La hora de finalización no tiene un formato válido.',

                'hora_fin.after' =>
                    'La hora final debe ser posterior a la hora inicial.',

                'ubicacion.required' =>
                    'Debe seleccionar la ubicación de la guardia.',

                'ubicacion.in' =>
                    'La ubicación debe ser TVC o CNT.',

                'observacion.string' =>
                    'La observación no es válida.',

                'observacion.max' =>
                    'La observación no puede superar los 500 caracteres.',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener agente válido
    |--------------------------------------------------------------------------
    |
    | La clave foránea garantiza que el usuario exista.
    | Esta validación adicional garantiza que:
    |
    | - La cuenta esté activa.
    | - El rol sea UsuarioTI.
    |
    */

    private function obtenerAgenteValido(
        int $usuarioId
    ): Usuario {
        $agente = Usuario::query()
            ->with('rol')
            ->whereKey(
                $usuarioId
            )
            ->where(
                'activo',
                true
            )
            ->whereHas(
                'rol',
                function (Builder $query) {
                    $query->where(
                        'nombre',
                        'UsuarioTI'
                    );
                }
            )
            ->first();

        if (! $agente) {
            throw ValidationException::withMessages([
                'usuario_id' =>
                    'El usuario seleccionado no es un agente de soporte activo.',
            ]);
        }

        return $agente;
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener mes y año
    |--------------------------------------------------------------------------
    */

    private function obtenerPeriodo(
        Request $request
    ): array {
        $mes = (int) $request->input(
            'mes',
            now()->month
        );

        $anio = (int) $request->input(
            'anio',
            now()->year
        );

        if (
            $mes < 1
            || $mes > 12
        ) {
            $mes = now()->month;
        }

        if (
            $anio < 2000
            || $anio > 2100
        ) {
            $anio = now()->year;
        }

        return [
            $mes,
            $anio,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener años disponibles
    |--------------------------------------------------------------------------
    */

    private function obtenerAniosDisponibles(): Collection
{
    $anios = GuardiaSoporte::query()
        ->selectRaw(
            'EXTRACT(YEAR FROM fecha)::integer AS anio'
        )
        ->distinct()
        ->orderByDesc('anio')
        ->pluck('anio');

    return $anios
        ->push(
            now()->year
        )
        ->unique()
        ->sortDesc()
        ->values();
}

/*
|--------------------------------------------------------------------------
| Notificar asignación de guardia
|--------------------------------------------------------------------------
|
| La notificación se envía únicamente al UsuarioTI asignado.
|
*/

private function notificarGuardiaAsignada(
    GuardiaSoporte $guardia
): void {
    try {
        $guardia->loadMissing([
            'agente.rol',
            'creador.rol',
        ]);

        if (! $guardia->agente) {
            Log::warning(
                'No se encontró el agente asignado para enviar la notificación de guardia.',
                [
                    'guardia_id' =>
                        $guardia->id,

                    'usuario_id' =>
                        $guardia->usuario_id,
                ]
            );

            return;
        }

        $guardia->agente->notify(
            new GuardiaAsignadaNotification(
                $guardia
            )
        );
    } catch (\Throwable $exception) {
        Log::error(
            'No se pudo enviar la notificación de asignación de guardia.',
            [
                'guardia_id' =>
                    $guardia->id,

                'usuario_id' =>
                    $guardia->usuario_id,

                'fecha' =>
                    $guardia->fecha?->format(
                        'Y-m-d'
                    ),

                'error' =>
                    $exception->getMessage(),
            ]
        );
    }
}


    /*
    |--------------------------------------------------------------------------
    | Autorizar administrador
    |--------------------------------------------------------------------------
    |
    | Las rutas ya están protegidas por middleware. Esta validación agrega
    | una segunda capa para las acciones que modifican asignaciones.
    |
    */

    private function autorizarAdministrador(): void
    {
        if (! auth()->user()?->esAdministrador()) {
            abort(
                403,
                'No tienes permiso para administrar las guardias.'
            );
        }
    }
}