<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistroRequest;
use App\Http\Requests\VerificarCodigoRequest;
use App\Mail\CodigoVerificacionMail;
use App\Mail\EnlaceMagicoMail;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Auth\TokenAutenticacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly TokenAutenticacionService $tokens
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Mostrar login
    |--------------------------------------------------------------------------
    */

    public function login(): View
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | Enviar enlace mágico
    |--------------------------------------------------------------------------
    */

    public function authenticate(
        LoginRequest $request
    ): RedirectResponse {
        $correo = $request->validated(
            'correo'
        );

        $usuario = Usuario::query()
            ->whereRaw(
                'LOWER(correo) = ?',
                [$correo]
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | No revelar si el correo existe
        |--------------------------------------------------------------------------
        */

        if (
            ! $usuario
            || ! $usuario->activo
        ) {
            return back()->with(
                'success',
                'Si el correo está registrado, recibirá un enlace para iniciar sesión.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Correo todavía no verificado
        |--------------------------------------------------------------------------
        */

        if (
            ! $usuario
                ->correoEstaVerificado()
        ) {
            return $this->enviarCodigoPendiente(
                $usuario
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generar y enviar enlace mágico
        |--------------------------------------------------------------------------
        */

        try {
            $token = $this->tokens
                ->generarTokenLogin(
                    $usuario
                );

            $url = route(
                'login.magic',
                [
                    'token' => $token,
                ]
            );

            Mail::to(
                $usuario->correo
            )->send(
                new EnlaceMagicoMail(
                    $usuario,
                    $url
                )
            );
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo enviar el enlace mágico.',
                [
                    'usuario_id' => $usuario->id,
                    'error' => $exception->getMessage(),
                ]
            );

            return back()
                ->withInput([
                    'correo' => $correo,
                ])
                ->withErrors([
                    'correo' =>
                        'No fue posible enviar el enlace de acceso. Intenta nuevamente.',
                ]);
        }

        return back()->with(
            'success',
            'Revisa tu correo. Te enviamos un enlace para iniciar sesión.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Consumir enlace mágico
    |--------------------------------------------------------------------------
    */

    public function magicLogin(
        string $token,
        Request $request
    ): RedirectResponse {
        /*
        | El servicio valida y consume el token,
        | autentica al usuario y regenera la sesión.
        */

        $this->tokens
            ->iniciarSesionConToken(
                $token,
                $request
            );

        /*
        | Si el middleware auth guardó una página pendiente,
        | el usuario será enviado a ella.
        |
        | Si no existe una página pendiente, se utilizará
        | el dashboard como destino predeterminado.
        */

        return redirect()
            ->intended(
                route('dashboard')
            )
            ->with(
                'success',
                'Sesión iniciada correctamente.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Mostrar registro
    |--------------------------------------------------------------------------
    */

    public function register(): View
    {
        return view(
            'auth.register'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar usuario
    |--------------------------------------------------------------------------
    */

    public function store(
        RegistroRequest $request
    ): RedirectResponse {
        $rolUsuario = Rol::query()
            ->where(
                'nombre',
                'Usuario'
            )
            ->first();

        if (! $rolUsuario) {
            return back()
                ->withInput()
                ->withErrors([
                    'registro' =>
                        'No existe un rol predeterminado para usuarios.',
                ]);
        }

        $usuario = Usuario::create([
            'nombre' =>
                $request->validated('nombre'),

            'correo' =>
                $request->validated('correo'),

            'rol_id' =>
                $rolUsuario->id,

            'activo' =>
                true,

            'correo_verificado_at' =>
                null,
        ]);

        /*
        | Guardar el correo temporalmente para mostrar
        | la pantalla de verificación.
        */

        $request->session()->put(
            'correo_verificacion',
            $usuario->correo
        );

        /*
        | Generar y enviar código de verificación.
        */

        try {
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
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo enviar el código de registro.',
                [
                    'usuario_id' => $usuario->id,
                    'error' => $exception->getMessage(),
                ]
            );

            return redirect()
                ->route(
                    'register.verification'
                )
                ->withErrors([
                    'codigo' =>
                        'La cuenta fue creada, pero no se pudo enviar el código. Solicita uno nuevo.',
                ]);
        }

        return redirect()
            ->route(
                'register.verification'
            )
            ->with(
                'success',
                'Enviamos un código de verificación a tu correo.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Mostrar pantalla de verificación
    |--------------------------------------------------------------------------
    */

    public function verification(): View|RedirectResponse
    {
        $correo = session(
            'correo_verificacion'
        );

        if (! $correo) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'correo' =>
                        'Primero debes completar el registro.',
                ]);
        }

        return view(
            'auth.verificar-correo',
            compact('correo')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar código de registro
    |--------------------------------------------------------------------------
    */

    public function verify(
        VerificarCodigoRequest $request
    ): RedirectResponse {
        $this->tokens
            ->verificarCodigoRegistro(
                $request->validated(
                    'correo'
                ),
                $request->validated(
                    'codigo'
                ),
                $request
            );

        /*
        | El correo ya fue verificado y no es necesario
        | conservarlo en la sesión.
        */

        $request
            ->session()
            ->forget(
                'correo_verificacion'
            );

        /*
        | El registro continúa enviando directamente
        | al dashboard, como estaba anteriormente.
        */

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Correo verificado correctamente.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Reenviar código
    |--------------------------------------------------------------------------
    */

    public function resendCode(
        Request $request
    ): RedirectResponse {
        $correo = mb_strtolower(
            trim(
                (string) (
                    $request->input(
                        'correo'
                    )
                    ?? session(
                        'correo_verificacion'
                    )
                )
            )
        );

        if ($correo === '') {
            return redirect()
                ->route('register')
                ->withErrors([
                    'correo' =>
                        'No se encontró el correo que debe verificarse.',
                ]);
        }

        $usuario = Usuario::query()
            ->whereRaw(
                'LOWER(correo) = ?',
                [$correo]
            )
            ->first();

        /*
        | No revelar información innecesaria sobre la cuenta.
        */

        if (
            ! $usuario
            || ! $usuario->activo
            || $usuario->correoEstaVerificado()
        ) {
            return back()->with(
                'success',
                'Si la cuenta está pendiente, recibirá un nuevo código.'
            );
        }

        try {
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
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo reenviar el código.',
                [
                    'usuario_id' => $usuario->id,
                    'error' => $exception->getMessage(),
                ]
            );

            return back()->withErrors([
                'codigo' =>
                    'No fue posible reenviar el código. Intenta nuevamente.',
            ]);
        }

        $request->session()->put(
            'correo_verificacion',
            $usuario->correo
        );

        return back()->with(
            'success',
            'Enviamos un nuevo código de verificación.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cerrar sesión
    |--------------------------------------------------------------------------
    */

    public function logout(
        Request $request
    ): RedirectResponse {
        Auth::logout();

        /*
        | Invalidar completamente la sesión autenticada.
        */

        $request
            ->session()
            ->invalidate();

        /*
        | Crear un token CSRF nuevo para futuras solicitudes.
        */

        $request
            ->session()
            ->regenerateToken();

        return redirect()
            ->route('login');
    }

    /*
    |--------------------------------------------------------------------------
    | Enviar código a una cuenta pendiente
    |--------------------------------------------------------------------------
    */

    private function enviarCodigoPendiente(
        Usuario $usuario
    ): RedirectResponse {
        session([
            'correo_verificacion' =>
                $usuario->correo,
        ]);

        try {
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
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo enviar el código pendiente.',
                [
                    'usuario_id' => $usuario->id,
                    'error' => $exception->getMessage(),
                ]
            );

            return redirect()
                ->route(
                    'register.verification'
                )
                ->withErrors([
                    'codigo' =>
                        'No fue posible enviar el código. Intenta reenviarlo.',
                ]);
        }

        return redirect()
            ->route(
                'register.verification'
            )
            ->with(
                'success',
                'Debes verificar tu correo. Te enviamos un nuevo código.'
            );
    }
}