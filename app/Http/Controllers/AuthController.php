<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistroRequest;
use App\Http\Requests\VerificarCodigoRequest;
use App\Mail\CodigoVerificacionMail;
use App\Mail\EnlaceMagicoMail;
use App\Models\EmailDelivery;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Auth\TokenAutenticacionService;
use App\Services\Mail\TrackedMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly TokenAutenticacionService $tokens,
        private readonly TrackedMailService $trackedMail
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
    ): JsonResponse|RedirectResponse {
        $correo = $request->validated('correo');

        $usuario = Usuario::query()
            ->whereRaw(
                'LOWER(correo) = ?',
                [$correo]
            )
            ->first();

        $mensajeGenerico =
            'Si el correo está registrado, recibirá las instrucciones de acceso correspondientes.';

        /*
        |--------------------------------------------------------------------------
        | Respuesta uniforme para evitar enumeración de cuentas
        |--------------------------------------------------------------------------
        |
        | La respuesta pública no indica si el correo existe, si la cuenta está
        | pendiente de verificación ni si el correo fue colocado en la cola.
        |
        */

        if (
            ! $usuario
            || ! $usuario->activo
        ) {
            return $this->respuestaAutenticacionGenerica(
                $request,
                $mensajeGenerico
            );
        }

        try {
            if (! $usuario->correoEstaVerificado()) {
                $this->procesarCodigoPendiente(
                    $usuario,
                    $request
                );
            } else {
                $token = $this->tokens
                    ->generarTokenLogin($usuario);

                $url = route(
                    'login.magic',
                    ['token' => $token]
                );

                $this->trackedMail->sendAsync(
                    emailable: $usuario,
                    mailable: new EnlaceMagicoMail(
                        $usuario,
                        $url
                    ),
                    recipientEmail: $usuario->correo,
                    mailType: 'enlace_magico_login',
                    recipientName: $usuario->nombre,
                    subject: 'Acceso al Portal TI',
                    metadata: [
                        'usuario_id' => $usuario->id,
                        'tipo' => 'login',
                        'url' => $url,
                    ]
                );
            }
        } catch (Throwable $exception) {
            Log::error(
                'No se pudo procesar la solicitud de acceso.',
                [
                    'usuario_id' => $usuario->id,
                    'exception' => $exception::class,
                    'error_code' => $exception->getCode(),
                ]
            );

            /*
            | No se devuelve el detalle al cliente para impedir que la respuesta
            | permita distinguir cuentas existentes de cuentas inexistentes.
            */
        }

        return $this->respuestaAutenticacionGenerica(
            $request,
            $mensajeGenerico
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
    ): JsonResponse|RedirectResponse {
        $rolUsuario = Rol::query()
            ->where(
                'nombre',
                'Usuario'
            )
            ->first();

        if (! $rolUsuario) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'No fue posible completar el registro. Intenta nuevamente.',
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'registro' =>
                        'No fue posible completar el registro. Intenta nuevamente.',
                ]);
        }

        $usuario = Usuario::create([
            'nombre' =>
                $request->validated(
                    'nombre'
                ),

            'correo' =>
                $request->validated(
                    'correo'
                ),

            'rol_id' =>
                $rolUsuario->id,

            'activo' =>
                true,

            'correo_verificado_at' =>
                null,
        ]);

        $request->session()->put(
            'correo_verificacion',
            $usuario->correo
        );

        try {
            $codigo = $this->tokens
                ->generarCodigoRegistro(
                    $usuario
                );

            $delivery = $this->trackedMail
                ->sendAsync(
                    emailable:
                        $usuario,

                    mailable:
                        new CodigoVerificacionMail(
                            $usuario,
                            $codigo
                        ),

                    recipientEmail:
                        $usuario->correo,

                    mailType:
                        'codigo_verificacion_registro',

                    recipientName:
                        $usuario->nombre,

                    subject:
                        'Verifica tu correo',

                    metadata: [
                        'usuario_id' =>
                            $usuario->id,

                        'tipo' =>
                            'registro',

                        'codigo' =>
                            $codigo,
                    ]
                );

            $this->guardarDeliveryEnSesion(
                $request,
                $delivery
            );

        } catch (Throwable $exception) {
            Log::error(
                'No se pudo colocar el código de registro en la cola.',
                [
                    'usuario_id' =>
                        $usuario->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );

            $mensaje =
                'La cuenta fue creada, pero no fue posible procesar el código. Solicita uno nuevo.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' =>
                        true,

                    'message' =>
                        $mensaje,

                    'redirect' =>
                        route(
                            'register.verification'
                        ),

                    'email' => [
                        'sent' =>
                            false,

                        'queued' =>
                            false,

                        'failed' =>
                            true,

                        'status' =>
                            'fallido',

                        'delivery_id' =>
                            null,
                    ],
                ]);
            }

            return redirect()
                ->route(
                    'register.verification'
                )
                ->withErrors([
                    'codigo' =>
                        $mensaje,
                ]);
        }

        $mensaje =
            $delivery->estaPendiente()
                ? 'Tu cuenta fue creada. El código de verificación se está procesando.'
                : 'Tu cuenta fue creada, pero no fue posible colocar el código en la cola.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' =>
                    true,

                'message' =>
                    $mensaje,

                'redirect' =>
                    route(
                        'register.verification'
                    ),

                'email' =>
                    $this->respuestaEmail(
                        $delivery
                    ),
            ]);
        }

        return redirect()
            ->route(
                'register.verification'
            )
            ->with(
                'success',
                $mensaje
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
    ): JsonResponse|RedirectResponse {
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'No se encontró el correo que debe verificarse.',
                ], 422);
            }

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

        $mensajeGenerico =
            'Si la cuenta está pendiente, recibirá un nuevo código.';

        if (
            ! $usuario
            || ! $usuario->activo
            || $usuario->correoEstaVerificado()
        ) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' =>
                        true,

                    'message' =>
                        $mensajeGenerico,

                    'email' => [
                        'sent' =>
                            false,

                        'queued' =>
                            false,

                        'failed' =>
                            false,

                        'status' =>
                            null,

                        'delivery_id' =>
                            null,
                    ],
                ]);
            }

            return back()->with(
                'success',
                $mensajeGenerico
            );
        }

        try {
            $codigo = $this->tokens
                ->generarCodigoRegistro(
                    $usuario
                );

            $delivery = $this->trackedMail
                ->sendAsync(
                    emailable:
                        $usuario,

                    mailable:
                        new CodigoVerificacionMail(
                            $usuario,
                            $codigo
                        ),

                    recipientEmail:
                        $usuario->correo,

                    mailType:
                        'codigo_verificacion_reenvio',

                    recipientName:
                        $usuario->nombre,

                    subject:
                        'Nuevo código de verificación',

                    metadata: [
                        'usuario_id' =>
                            $usuario->id,

                        'tipo' =>
                            'reenvio',

                        'codigo' =>
                            $codigo,
                    ]
                );

            $request->session()->put(
                'correo_verificacion',
                $usuario->correo
            );

            $this->guardarDeliveryEnSesion(
                $request,
                $delivery
            );

        } catch (Throwable $exception) {
            Log::error(
                'No se pudo colocar el reenvío del código en la cola.',
                [
                    'usuario_id' =>
                        $usuario->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'No fue posible reenviar el código. Intenta nuevamente.',
                ], 500);
            }

            return back()->withErrors([
                'codigo' =>
                    'No fue posible reenviar el código. Intenta nuevamente.',
            ]);
        }

        $mensaje =
            $delivery->estaPendiente()
                ? 'El nuevo código de verificación se está procesando.'
                : 'No fue posible colocar el nuevo código en la cola de correo.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' =>
                    true,

                'message' =>
                    $mensaje,

                'email' =>
                    $this->respuestaEmail(
                        $delivery
                    ),
            ]);
        }

        return back()->with(
            'success',
            $mensaje
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

    private function procesarCodigoPendiente(
        Usuario $usuario,
        Request $request
    ): void {
        $request->session()->put(
            'correo_verificacion',
            $usuario->correo
        );

        $codigo = $this->tokens
            ->generarCodigoRegistro($usuario);

        $this->trackedMail->sendAsync(
            emailable: $usuario,
            mailable: new CodigoVerificacionMail(
                $usuario,
                $codigo
            ),
            recipientEmail: $usuario->correo,
            mailType: 'codigo_verificacion_pendiente',
            recipientName: $usuario->nombre,
            subject: 'Verifica tu correo',
            metadata: [
                'usuario_id' => $usuario->id,
                'tipo' => 'cuenta_pendiente',
                'codigo' => $codigo,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Respuesta pública uniforme del login
    |--------------------------------------------------------------------------
    */

    private function respuestaAutenticacionGenerica(
        Request $request,
        string $mensaje
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()
                ->json([
                    'success' => true,
                    'message' => $mensaje,
                ])
                ->header(
                    'Cache-Control',
                    'no-store, no-cache, must-revalidate, private'
                )
                ->header('Pragma', 'no-cache');
        }

        return back()->with(
            'success',
            $mensaje
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Consultar estado de correo de autenticación
    |--------------------------------------------------------------------------
    */

    public function emailStatus(
        Request $request,
        EmailDelivery $emailDelivery
    ): JsonResponse {
        $deliverySesion =
            (int) $request->session()->get(
                'auth_email_delivery_id',
                0
            );

        abort_unless(
            $deliverySesion > 0
            && $deliverySesion
                === (int) $emailDelivery->id,
            403,
            'No tienes permiso para consultar este envío.'
        );

        return response()->json([
            'success' =>
                true,

            'email' => [
                'sent' =>
                    $emailDelivery->status
                        === 'enviado',

                'queued' =>
                    in_array(
                        $emailDelivery->status,
                        [
                            'pendiente',
                            'enviando',
                        ],
                        true
                    ),

                'failed' =>
                    $emailDelivery->status
                        === 'fallido',

                'status' =>
                    $emailDelivery->status,

                'attempts' =>
                    $emailDelivery->attempts,

                'sent_at' =>
                    $emailDelivery->sent_at
                        ?->toIso8601String(),

                'failed_at' =>
                    $emailDelivery->failed_at
                        ?->toIso8601String(),
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar delivery autorizado en sesión
    |--------------------------------------------------------------------------
    */

    private function guardarDeliveryEnSesion(
        Request $request,
        EmailDelivery $delivery
    ): void {
        $request->session()->put(
            'auth_email_delivery_id',
            $delivery->id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Estructura común de respuesta del correo
    |--------------------------------------------------------------------------
    */

    private function respuestaEmail(
        EmailDelivery $delivery
    ): array {
        return [
            'sent' =>
                $delivery->status
                    === 'enviado',

            'queued' =>
                in_array(
                    $delivery->status,
                    [
                        'pendiente',
                        'enviando',
                    ],
                    true
                ),

            'failed' =>
                $delivery->status
                    === 'fallido',

            'status' =>
                $delivery->status,

            'delivery_id' =>
                $delivery->id,

            'attempts' =>
                $delivery->attempts,
        ];
    }

}