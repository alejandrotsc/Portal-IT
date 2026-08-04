<?php

namespace App\Http\Controllers;

use App\Mail\CodigoVerificacionMail;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Auth\TokenAutenticacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly TokenAutenticacionService $tokens
    ) {}


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

        $rolSeleccionado = $request->input(
            'rol'
        );

        $estadoSeleccionado = $request->input(
            'estado'
        );


        $usuarios = Usuario::query()
            ->with('rol')

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
                                    'LOWER(nombre) LIKE ?',
                                    ["%{$termino}%"]
                                )
                                ->orWhereRaw(
                                    'LOWER(correo) LIKE ?',
                                    ["%{$termino}%"]
                                );
                        }
                    );
                }
            )

            ->when(
                filled($rolSeleccionado),
                fn ($query) => $query->where(
                    'rol_id',
                    $rolSeleccionado
                )
            )

            ->when(
                $estadoSeleccionado === 'activo',
                fn ($query) => $query->where(
                    'activo',
                    true
                )
            )

            ->when(
                $estadoSeleccionado === 'inactivo',
                fn ($query) => $query->where(
                    'activo',
                    false
                )
            )

            ->when(
                $estadoSeleccionado === 'pendiente',
                fn ($query) => $query
                    ->where('activo', true)
                    ->whereNull(
                        'correo_verificado_at'
                    )
            )

            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Roles visibles en el filtro
        |--------------------------------------------------------------------------
        |
        | Únicamente se muestran:
        |
        | 1 = Usuario
        | 3 = Administrador
        |
        | UsuarioTI permanece en la base de datos, pero no aparece como opción
        | administrable dentro de este módulo.
        |
        */

        $roles = $this->rolesDisponibles();


        $resumen = [
            'total' => Usuario::count(),

            'activos' => Usuario::where(
                'activo',
                true
            )->count(),

            'inactivos' => Usuario::where(
                'activo',
                false
            )->count(),

            'pendientes' => Usuario::where(
                'activo',
                true
            )
                ->whereNull(
                    'correo_verificado_at'
                )
                ->count(),
        ];


        return view(
            'usuarios.index',
            compact(
                'usuarios',
                'roles',
                'resumen',
                'busqueda',
                'rolSeleccionado',
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
            'usuarios.create',
            [
                'roles' => $this->rolesDisponibles(),
            ]
        );
    }


    public function store(
        Request $request
    ): RedirectResponse {
        $this->normalizarDatos(
            $request
        );

        $validated = $this->validarUsuario(
            $request
        );


        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],

            'correo' => $validated['correo'],

            'rol_id' => $validated['rol_id'],

            'activo' => true,

            'correo_verificado_at' => null,
        ]);


        try {
            $this->enviarCodigo(
                $usuario
            );
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo enviar el código al usuario creado por administración.',
                [
                    'usuario_id' => $usuario->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );

            return redirect()
                ->route('usuarios.index')
                ->with(
                    'warning',
                    'El usuario fue creado, pero no se pudo enviar el código de verificación.'
                );
        }


        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario creado correctamente. Se envió un código de verificación a su correo.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    public function edit(
        Usuario $usuario
    ): View {
        return view(
            'usuarios.edit',
            [
                'usuario' => $usuario,

                'roles' =>
                    $this->rolesDisponibles(),
            ]
        );
    }


    public function update(
        Request $request,
        Usuario $usuario
    ): RedirectResponse {
        $this->normalizarDatos(
            $request
        );

        $validated = $this->validarUsuario(
            $request,
            $usuario
        );


        /*
        |--------------------------------------------------------------------------
        | Proteger el rol del administrador autenticado
        |--------------------------------------------------------------------------
        */

        if (Auth::id() === $usuario->id) {
            $rolAdministrador = Rol::query()
                ->where(
                    'nombre',
                    'Administrador'
                )
                ->first();

            if (
                ! $rolAdministrador
                || (int) $validated['rol_id']
                    !== (int) $rolAdministrador->id
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'rol_id' =>
                            'No puedes retirar el rol de administrador de tu propia cuenta.',
                    ]);
            }
        }


        $correoCambio = mb_strtolower(
            $usuario->correo
        ) !== $validated['correo'];


        $usuario->update([
            'nombre' => $validated['nombre'],

            'correo' => $validated['correo'],

            'rol_id' => $validated['rol_id'],

            'correo_verificado_at' =>
                $correoCambio
                    ? null
                    : $usuario
                        ->correo_verificado_at,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Invalidar tokens si cambió el correo
        |--------------------------------------------------------------------------
        */

        if ($correoCambio) {
            $this->invalidarTokens(
                $usuario
            );

            try {
                $this->enviarCodigo(
                    $usuario
                );
            } catch (Throwable $exception) {
                Log::error(
                    'No se pudo enviar el código después de cambiar el correo.',
                    [
                        'usuario_id' =>
                            $usuario->id,

                        'error' =>
                            $exception->getMessage(),
                    ]
                );

                return redirect()
                    ->route('usuarios.index')
                    ->with(
                        'warning',
                        'El usuario fue actualizado, pero no se pudo enviar el código al nuevo correo.'
                    );
            }
        }


        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                $correoCambio
                    ? 'Usuario actualizado. El nuevo correo debe verificarse.'
                    : 'Usuario actualizado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Activar o desactivar
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        Usuario $usuario
    ): RedirectResponse {
        if (Auth::id() === $usuario->id) {
            return back()->withErrors([
                'usuario' =>
                    'No puedes cambiar el estado de tu propia cuenta.',
            ]);
        }


        $usuario->update([
            'activo' => ! $usuario->activo,
        ]);


        if (! $usuario->activo) {
            $this->invalidarTokens(
                $usuario
            );
        }


        return back()->with(
            'success',
            $usuario->activo
                ? 'Usuario activado correctamente.'
                : 'Usuario desactivado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reenviar verificación
    |--------------------------------------------------------------------------
    */

    public function resendVerification(
        Usuario $usuario
    ): RedirectResponse {
        if (! $usuario->activo) {
            return back()->withErrors([
                'usuario' =>
                    'No se puede verificar una cuenta desactivada.',
            ]);
        }


        if ($usuario->correoEstaVerificado()) {
            return back()->with(
                'success',
                'El correo de este usuario ya está verificado.'
            );
        }


        try {
            $this->enviarCodigo(
                $usuario
            );
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo reenviar el código desde administración.',
                [
                    'usuario_id' => $usuario->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );

            return back()->withErrors([
                'usuario' =>
                    'No fue posible enviar el código de verificación.',
            ]);
        }


        return back()->with(
            'success',
            'Se envió un nuevo código de verificación.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Roles disponibles
    |--------------------------------------------------------------------------
    |
    | Se centraliza la consulta para evitar repetirla en index, create y edit.
    | El rol UsuarioTI no se elimina de la tabla, únicamente deja de aparecer
    | como una opción seleccionable desde la administración de usuarios.
    |
    */

    private function rolesDisponibles()
    {
        return Rol::query()
            ->whereIn(
                'id',
                [
                    1,
                    2,
                    3,
                ]
            )
            ->orderBy('nombre')
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar datos
    |--------------------------------------------------------------------------
    */

    private function normalizarDatos(
        Request $request
    ): void {
        $request->merge([
            'nombre' => preg_replace(
                '/\s+/',
                ' ',
                trim(
                    (string) $request->nombre
                )
            ),

            'correo' => mb_strtolower(
                trim(
                    (string) $request->correo
                )
            ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validar usuario
    |--------------------------------------------------------------------------
    */

    private function validarUsuario(
        Request $request,
        ?Usuario $usuario = null
    ): array {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\s.\'-]+$/u',
            ],

            'correo' => [
                'required',
                'string',
                'email:rfc',
                'max:200',

                Rule::unique(
                    'usuarios',
                    'correo'
                )->ignore(
                    $usuario?->id
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | Roles permitidos
            |--------------------------------------------------------------------------
            |
            | Se valida también en el servidor para impedir que alguien pueda
            | enviar manualmente rol_id=2 modificando el formulario.
            |
            */

            'rol_id' => [
                'required',
                'integer',
                Rule::in([
                    1,
                    2,
                    3,
                ]),
            ],
        ], [
            'nombre.required' =>
                'Debe ingresar el nombre completo.',

            'nombre.min' =>
                'El nombre debe tener al menos 3 caracteres.',

            'nombre.max' =>
                'El nombre no puede superar los 200 caracteres.',

            'nombre.regex' =>
                'El nombre contiene caracteres no permitidos.',

            'correo.required' =>
                'Debe ingresar el correo electrónico.',

            'correo.email' =>
                'Debe ingresar un correo electrónico válido.',

            'correo.max' =>
                'El correo no puede superar los 200 caracteres.',

            'correo.unique' =>
                'El correo ya pertenece a otro usuario.',

            'rol_id.required' =>
                'Debe seleccionar un rol.',

            'rol_id.integer' =>
                'El rol seleccionado no es válido.',

            'rol_id.in' =>
                'El rol seleccionado no es válido.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Invalidar tokens
    |--------------------------------------------------------------------------
    */

    private function invalidarTokens(
        Usuario $usuario
    ): void {
        $usuario->tokensAutenticacion()
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),

                'updated_at' => now(),
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Enviar código
    |--------------------------------------------------------------------------
    */

    private function enviarCodigo(
        Usuario $usuario
    ): void {
        $codigo = $this->tokens
            ->generarCodigoRegistro(
                $usuario
            );

        Mail::to(
            $usuario->correo
        )->send(
            new CodigoVerificacionMail(
                $usuario,
                $codigo
            )
        );
    }
}