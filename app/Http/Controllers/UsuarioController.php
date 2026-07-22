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

    public function index(): View
    {
        $usuarios = Usuario::query()
            ->with('rol')
            ->orderByDesc('id')
            ->get();

        return view(
            'usuarios.index',
            compact('usuarios')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $roles = Rol::query()
            ->orderBy('nombre')
            ->get();

        return view(
            'usuarios.create',
            compact('roles')
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $request->merge([
            'nombre' => preg_replace(
                '/\s+/',
                ' ',
                trim((string) $request->nombre)
            ),

            'correo' => mb_strtolower(
                trim((string) $request->correo)
            ),
        ]);

        $validated = $request->validate([
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
                'unique:usuarios,correo',
            ],

            'rol_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
        ], [
            'nombre.required' =>
                'Debe ingresar el nombre completo.',

            'nombre.min' =>
                'El nombre debe tener al menos 3 caracteres.',

            'nombre.regex' =>
                'El nombre contiene caracteres no permitidos.',

            'correo.required' =>
                'Debe ingresar el correo electrónico.',

            'correo.email' =>
                'Debe ingresar un correo electrónico válido.',

            'correo.unique' =>
                'El correo ya está registrado.',

            'rol_id.required' =>
                'Debe seleccionar un rol.',

            'rol_id.exists' =>
                'El rol seleccionado no es válido.',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'correo' => $validated['correo'],
            'rol_id' => $validated['rol_id'],
            'activo' => true,
            'correo_verificado_at' => null,
        ]);

        try {
            $this->enviarCodigo($usuario);
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo enviar el código al usuario creado por administración.',
                [
                    'usuario_id' => $usuario->id,
                    'error' => $exception->getMessage(),
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
                'Usuario creado. Se envió un código de verificación a su correo.'
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
        $roles = Rol::query()
            ->orderBy('nombre')
            ->get();

        return view(
            'usuarios.edit',
            compact(
                'usuario',
                'roles'
            )
        );
    }

    public function update(
        Request $request,
        Usuario $usuario
    ): RedirectResponse {
        $request->merge([
            'nombre' => preg_replace(
                '/\s+/',
                ' ',
                trim((string) $request->nombre)
            ),

            'correo' => mb_strtolower(
                trim((string) $request->correo)
            ),
        ]);

        $validated = $request->validate([
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
                )->ignore($usuario->id),
            ],

            'rol_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
        ], [
            'nombre.required' =>
                'Debe ingresar el nombre completo.',

            'nombre.min' =>
                'El nombre debe tener al menos 3 caracteres.',

            'nombre.regex' =>
                'El nombre contiene caracteres no permitidos.',

            'correo.required' =>
                'Debe ingresar el correo electrónico.',

            'correo.email' =>
                'Debe ingresar un correo electrónico válido.',

            'correo.unique' =>
                'El correo ya pertenece a otro usuario.',

            'rol_id.required' =>
                'Debe seleccionar un rol.',

            'rol_id.exists' =>
                'El rol seleccionado no es válido.',
        ]);

        $correoAnterior = $usuario->correo;

        $correoCambio = mb_strtolower($correoAnterior)
            !== $validated['correo'];

        $usuario->update([
            'nombre' => $validated['nombre'],
            'correo' => $validated['correo'],
            'rol_id' => $validated['rol_id'],

            'correo_verificado_at' => $correoCambio
                ? null
                : $usuario->correo_verificado_at,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Si cambió el correo, invalidar tokens y verificar el nuevo correo
        |--------------------------------------------------------------------------
        */

        if ($correoCambio) {
            $usuario->tokensAutenticacion()
                ->whereNull('used_at')
                ->update([
                    'used_at' => now(),
                    'updated_at' => now(),
                ]);

            try {
                $this->enviarCodigo($usuario);
            } catch (Throwable $exception) {
                Log::error(
                    'No se pudo enviar el código después de cambiar el correo.',
                    [
                        'usuario_id' => $usuario->id,
                        'error' => $exception->getMessage(),
                    ]
                );

                return redirect()
                    ->route('usuarios.index')
                    ->with(
                        'warning',
                        'Usuario actualizado, pero no se pudo enviar el código al nuevo correo.'
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
    | Cambiar estado
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        Usuario $usuario
    ): RedirectResponse {
        if (Auth::id() === $usuario->id) {
            return back()->withErrors([
                'usuario' =>
                    'No puedes desactivar tu propia cuenta.',
            ]);
        }

        $usuario->update([
            'activo' => ! $usuario->activo,
        ]);

        if (! $usuario->activo) {
            $usuario->tokensAutenticacion()
                ->whereNull('used_at')
                ->update([
                    'used_at' => now(),
                    'updated_at' => now(),
                ]);
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
            $this->enviarCodigo($usuario);
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo reenviar el código desde administración.',
                [
                    'usuario_id' => $usuario->id,
                    'error' => $exception->getMessage(),
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
    | Desactivar en lugar de eliminar
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Usuario $usuario
    ): RedirectResponse {
        if (Auth::id() === $usuario->id) {
            return back()->withErrors([
                'usuario' =>
                    'No puedes desactivar tu propia cuenta.',
            ]);
        }

        $usuario->update([
            'activo' => false,
        ]);

        $usuario->tokensAutenticacion()
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario desactivado correctamente.'
            );
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
            ->generarCodigoRegistro($usuario);

        Mail::to($usuario->correo)->send(
            new CodigoVerificacionMail(
                $usuario,
                $codigo
            )
        );
    }
}