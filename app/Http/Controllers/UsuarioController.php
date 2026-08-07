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

/*
|--------------------------------------------------------------------------
| Controlador de usuarios
|--------------------------------------------------------------------------
|
| Gestiona la administración de cuentas del Portal TI: listado, creación,
| edición, roles, estados, verificación de correo, extensión telefónica e
| invalidación de tokens de autenticación.
|
*/

class UsuarioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dependencias
    |--------------------------------------------------------------------------
    |
    | Recibe el servicio utilizado para generar códigos y administrar tokens
    | asociados con los procesos de autenticación y verificación.
    |
    */

    public function __construct(
        private readonly TokenAutenticacionService $tokens
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Listado
    |--------------------------------------------------------------------------
    |
    | Construye el listado administrativo de usuarios aplicando búsqueda, filtros por rol y estado, paginación y resumen general.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar listado de usuarios
    |--------------------------------------------------------------------------
    |
    | Aplica búsqueda y filtros, calcula indicadores generales y prepara el
    | listado paginado para el panel administrativo.
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
                                )
                                ->orWhereRaw(
                                    'LOWER(COALESCE(extension_telefonica, \'\')) LIKE ?',
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
                    ->where(
                        'activo',
                        true
                    )
                    ->whereNull(
                        'correo_verificado_at'
                    )
            )

            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();


        $roles = $this->rolesDisponibles();


        $resumen = [
            'total' => Usuario::count(),

            'activos' => Usuario::query()
                ->where(
                    'activo',
                    true
                )
                ->count(),

            'inactivos' => Usuario::query()
                ->where(
                    'activo',
                    false
                )
                ->count(),

            'pendientes' => Usuario::query()
                ->where(
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
    |
    | Presenta el formulario de creación con los roles disponibles para asignación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar formulario de creación
    |--------------------------------------------------------------------------
    |
    | Renderiza la vista para registrar un usuario y proporciona los roles
    | disponibles.
    |
    */

    public function create(): View
    {
        return view(
            'usuarios.create',
            [
                'roles' =>
                    $this->rolesDisponibles(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Registrar usuario
    |--------------------------------------------------------------------------
    |
    | Normaliza y valida la información, crea la cuenta y envía el código de
    | verificación correspondiente.
    |
    */

    public function store(
        Request $request
    ): RedirectResponse {
        $this->normalizarDatos(
            $request
        );

        $validated = $this->validarUsuario(
            $request
        );

        $extensionTelefonica =
            $this->resolverExtensionTelefonica(
                (int) $validated['rol_id'],
                $validated['extension_telefonica']
                    ?? null
            );


        $usuario = Usuario::create([
            'nombre' =>
                $validated['nombre'],

            'correo' =>
                $validated['correo'],

            'rol_id' =>
                $validated['rol_id'],

            'extension_telefonica' =>
                $extensionTelefonica,

            'activo' =>
                true,

            'correo_verificado_at' =>
                null,
        ]);


        try {
            $this->enviarCodigo(
                $usuario
            );
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo enviar el código al usuario creado por administración.',
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
    |
    | Presenta el formulario de edición junto con los roles permitidos.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mostrar formulario de edición
    |--------------------------------------------------------------------------
    |
    | Prepara la cuenta seleccionada y los roles permitidos para su edición.
    |
    */

    public function edit(
        Usuario $usuario
    ): View {
        return view(
            'usuarios.edit',
            [
                'usuario' =>
                    $usuario,

                'roles' =>
                    $this->rolesDisponibles(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar usuario
    |--------------------------------------------------------------------------
    |
    | Valida cambios, protege el rol administrativo propio, resuelve la
    | extensión telefónica y reinicia la verificación cuando cambia el correo.
    |
    */

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
                    !==
                    (int) $rolAdministrador->id
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'rol_id' =>
                            'No puedes retirar el rol de administrador de tu propia cuenta.',
                    ]);
            }
        }


        $correoCambio =
            mb_strtolower(
                $usuario->correo
            )
            !==
            $validated['correo'];


        $extensionTelefonica =
            $this->resolverExtensionTelefonica(
                (int) $validated['rol_id'],
                $validated['extension_telefonica']
                    ?? null
            );


        $usuario->update([
            'nombre' =>
                $validated['nombre'],

            'correo' =>
                $validated['correo'],

            'rol_id' =>
                $validated['rol_id'],

            'extension_telefonica' =>
                $extensionTelefonica,

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
    |
    | Alterna el estado de la cuenta e invalida tokens pendientes cuando el usuario queda desactivado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cambiar estado de usuario
    |--------------------------------------------------------------------------
    |
    | Evita modificar la propia cuenta, alterna el estado activo e invalida
    | tokens pendientes cuando se desactiva al usuario.
    |
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
            'activo' =>
                ! $usuario->activo,
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
    |
    | Genera y envía un nuevo código únicamente para cuentas activas que aún no han verificado su correo.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Reenviar código de verificación
    |--------------------------------------------------------------------------
    |
    | Comprueba que la cuenta esté activa y pendiente antes de generar y enviar
    | un nuevo código.
    |
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
                    'usuario_id' =>
                        $usuario->id,

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
    | Obtiene únicamente los roles permitidos para administración de usuarios dentro del Portal TI.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Obtener roles permitidos
    |--------------------------------------------------------------------------
    |
    | Devuelve los roles que pueden administrarse desde este módulo.
    |
    */

    private function rolesDisponibles()
    {
        return Rol::query()
            ->whereIn(
                'nombre',
                [
                    'Usuario',
                    'UsuarioTI',
                    'Administrador',
                ]
            )
            ->orderBy('nombre')
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar datos
    |--------------------------------------------------------------------------
    |
    | Limpia nombre, correo y extensión telefónica antes de ejecutar la validación.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Normalizar información del usuario
    |--------------------------------------------------------------------------
    |
    | Limpia espacios, normaliza el correo a minúsculas y prepara la extensión
    | telefónica antes de validar.
    |
    */

    private function normalizarDatos(
        Request $request
    ): void {
        $extensionTelefonica = preg_replace(
            '/\s+/',
            '',
            trim(
                (string) $request->input(
                    'extension_telefonica',
                    ''
                )
            )
        );

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

            'extension_telefonica' =>
                $extensionTelefonica !== ''
                    ? $extensionTelefonica
                    : null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validar usuario
    |--------------------------------------------------------------------------
    |
    | Aplica reglas de nombre, correo, rol y extensión telefónica según el tipo de usuario seleccionado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Validar datos del usuario
    |--------------------------------------------------------------------------
    |
    | Centraliza las reglas utilizadas en creación y edición, incluyendo la
    | obligatoriedad de extensión para UsuarioTI.
    |
    */

    private function validarUsuario(
        Request $request,
        ?Usuario $usuario = null
    ): array {
        $rolUsuarioTI = Rol::query()
            ->where(
                'nombre',
                'UsuarioTI'
            )
            ->value('id');

        $rolesPermitidos =
            $this->rolesDisponibles()
                ->pluck('id')
                ->map(
                    fn ($id) => (int) $id
                )
                ->all();


        return $request->validate(
            [
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

                'rol_id' => [
                    'required',
                    'integer',

                    Rule::in(
                        $rolesPermitidos
                    ),
                ],

                'extension_telefonica' => [
                    Rule::requiredIf(
                        $rolUsuarioTI !== null
                        && (int) $request->input(
                            'rol_id'
                        )
                        ===
                        (int) $rolUsuarioTI
                    ),

                    'nullable',
                    'string',
                    'max:10',
                    'regex:/^[0-9]+$/',
                ],
            ],
            [
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

                'extension_telefonica.required' =>
                    'Debe ingresar la extensión telefónica del usuario de soporte.',

                'extension_telefonica.string' =>
                    'La extensión telefónica no es válida.',

                'extension_telefonica.max' =>
                    'La extensión telefónica no puede superar los 10 caracteres.',

                'extension_telefonica.regex' =>
                    'La extensión telefónica solo puede contener números.',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolver extensión telefónica
    |--------------------------------------------------------------------------
    |
    | Únicamente los usuarios con rol UsuarioTI conservan una extensión.
    | Para cualquier otro rol se devuelve NULL antes de persistir los datos.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Resolver extensión según rol
    |--------------------------------------------------------------------------
    |
    | Devuelve la extensión únicamente cuando el rol seleccionado corresponde
    | a UsuarioTI.
    |
    */

    private function resolverExtensionTelefonica(
        int $rolId,
        ?string $extensionTelefonica
    ): ?string {
        $rolUsuarioTIId = Rol::query()
            ->where(
                'nombre',
                'UsuarioTI'
            )
            ->value('id');

        if (
            $rolUsuarioTIId === null
            || $rolId !== (int) $rolUsuarioTIId
        ) {
            return null;
        }

        $extensionTelefonica = trim(
            (string) $extensionTelefonica
        );

        return $extensionTelefonica !== ''
            ? $extensionTelefonica
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Invalidar tokens
    |--------------------------------------------------------------------------
    |
    | Marca como utilizados todos los tokens de autenticación pendientes pertenecientes al usuario.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Revocar tokens pendientes
    |--------------------------------------------------------------------------
    |
    | Marca como utilizados los tokens aún vigentes para impedir su reutilización.
    |
    */

    private function invalidarTokens(
        Usuario $usuario
    ): void {
        $usuario->tokensAutenticacion()
            ->whereNull('used_at')
            ->update([
                'used_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Enviar código
    |--------------------------------------------------------------------------
    |
    | Genera un código de verificación y lo envía al correo asociado con la cuenta.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Generar y enviar código
    |--------------------------------------------------------------------------
    |
    | Solicita un nuevo código al servicio de autenticación y lo envía mediante
    | el mailable de verificación.
    |
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