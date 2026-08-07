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

/*
|--------------------------------------------------------------------------
| Controlador de guardias de soporte
|--------------------------------------------------------------------------
|
| Gestiona la asignación y consulta de guardias de fin de semana del Portal TI,
| incluyendo validación de agentes, períodos, horarios, estados y notificaciones.
|
*/

class GuardiaSoporteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Administración de guardias
    |--------------------------------------------------------------------------
    |
    | Prepara el panel administrativo con agentes disponibles, período
    | seleccionado y asignaciones registradas.
    |
    | Vista disponible únicamente para el Administrador.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar administración de guardias
    |--------------------------------------------------------------------------
    |
    | Resuelve el período consultado, agentes disponibles y asignaciones
    | registradas antes de renderizar el panel administrativo.
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
    | Presenta las asignaciones activas según el nivel de acceso.
    |
    | UsuarioTI:
    | - Consulta únicamente sus propias guardias.
    |
    | Administrador:
    | - Puede consultar el calendario general.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar guardias según el rol
    |--------------------------------------------------------------------------
    |
    | Construye la consulta del período y aplica las restricciones necesarias
    | para UsuarioTI o Administrador.
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
|
| Valida la información recibida, comprueba que el agente sea UsuarioTI, registra la asignación y envía la notificación correspondiente.
|
*/

/*
|--------------------------------------------------------------------------
| Registrar nueva guardia
|--------------------------------------------------------------------------
|
| Valida la solicitud, comprueba el agente seleccionado, crea la asignación
| y notifica al UsuarioTI correspondiente.
|
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
    |
    | Realiza una validación adicional sobre la cuenta seleccionada para confirmar que esté activa y pertenezca al rol requerido.
    |
    */

    $this->obtenerAgenteValido(
        (int) $validated['usuario_id']
    );

    /*
    |--------------------------------------------------------------------------
    | Registrar guardia
    |--------------------------------------------------------------------------
    |
    | Crea la asignación con agente, creador, fecha, horario, ubicación, observación y estado activo.
    |
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
    |
    | Envía una notificación al UsuarioTI seleccionado una vez registrada correctamente la guardia.
    |
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
|
| Valida y modifica una guardia existente, detectando si hubo cambio de agente para evitar notificaciones innecesarias.
|
*/

/*
|--------------------------------------------------------------------------
| Modificar guardia existente
|--------------------------------------------------------------------------
|
| Actualiza la asignación y determina si el agente cambió para decidir si debe
| enviarse una nueva notificación.
|
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
    |
    | Realiza una validación adicional sobre la cuenta seleccionada para confirmar que esté activa y pertenezca al rol requerido.
    |
    */

    $this->obtenerAgenteValido(
        (int) $validated['usuario_id']
    );

    /*
    |--------------------------------------------------------------------------
    | Comprobar si la guardia fue reasignada
    |--------------------------------------------------------------------------
    |
    | Compara el agente anterior con el nuevo para determinar si corresponde enviar una nueva notificación.
    |
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
    |
    | Actualiza los datos operativos de la guardia manteniendo el registro existente.
    |
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
    |
    | Evita avisos duplicados y notifica solamente al nuevo agente cuando ocurre una reasignación.
    |
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
    |
    | Alterna el estado activo de la asignación y evita reactivar guardias cuya fecha ya pasó.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cambiar estado de guardia
    |--------------------------------------------------------------------------
    |
    | Activa o cancela una guardia existente aplicando la restricción sobre
    | fechas ya transcurridas.
    |
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
    |
    | Aplica reglas de agente, fecha, fin de semana, unicidad, horario, ubicación y observación antes de guardar cambios.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Validar información de guardia
    |--------------------------------------------------------------------------
    |
    | Centraliza las reglas de negocio y mensajes de validación utilizados
    | tanto al crear como al actualizar una asignación.
    |
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
    | La clave foránea garantiza que el usuario exista. Esta validación
    | adicional confirma que el agente pueda ser asignado operativamente.
    |
    | Requisitos:
    | - Cuenta activa.
    | - Rol UsuarioTI.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Validar agente seleccionado
    |--------------------------------------------------------------------------
    |
    | Devuelve el UsuarioTI activo correspondiente o genera un error de
    | validación cuando la cuenta no puede asumir la guardia.
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
    |
    | Normaliza el período solicitado y aplica valores actuales cuando el mes o año recibido se encuentra fuera del rango permitido.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Resolver período consultado
    |--------------------------------------------------------------------------
    |
    | Obtiene mes y año desde la solicitud y corrige valores fuera del rango
    | permitido utilizando el período actual.
    |
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
    |
    | Construye la lista de años existentes en las guardias e incluye siempre el año actual.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Construir años disponibles
    |--------------------------------------------------------------------------
    |
    | Recupera años existentes en la base de datos, añade el año actual y
    | devuelve la colección sin duplicados y ordenada de forma descendente.
    |
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
| Carga las relaciones necesarias y envía la notificación únicamente al
| UsuarioTI asignado. Cualquier fallo se registra sin impedir la operación.
|
*/

/*
|--------------------------------------------------------------------------
| Enviar notificación de guardia
|--------------------------------------------------------------------------
|
| Notifica al agente asignado y registra advertencias o errores sin bloquear
| el proceso principal de creación o actualización.
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
    | Las rutas ya están protegidas por middleware. Esta validación añade una
    | segunda capa para las acciones que administran o modifican guardias.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Verificar permisos administrativos
    |--------------------------------------------------------------------------
    |
    | Confirma que el usuario autenticado tenga privilegios de Administrador
    | antes de permitir acciones de gestión sobre las guardias.
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