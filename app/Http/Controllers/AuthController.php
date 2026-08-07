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

/*
|--------------------------------------------------------------------------
| Controlador de autenticación
|--------------------------------------------------------------------------
|
| Gestiona el acceso al Portal TI mediante enlaces mágicos, el registro de
| usuarios, la verificación de correo, el reenvío de códigos, el seguimiento
| de entregas de correo y el cierre seguro de sesión.
|
*/

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dependencias
    |--------------------------------------------------------------------------
    |
    | Recibe los servicios responsables de generar y validar tokens de acceso
    | y de registrar y procesar envíos de correo de forma asíncrona.
    |
    */

    public function __construct(
        private readonly TokenAutenticacionService $tokens,
        private readonly TrackedMailService $trackedMail
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Mostrar login
    |--------------------------------------------------------------------------
    |
    | Presenta la vista principal utilizada para solicitar acceso mediante
    | correo corporativo.
    |
    */

    public function login(): View
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | Enviar enlace mágico
    |--------------------------------------------------------------------------
    |
    | Busca la cuenta asociada al correo recibido y, cuando corresponde,
    | genera un enlace mágico o reenvía un código de verificación pendiente.
    | La respuesta pública se mantiene uniforme para evitar enumeración de
    | cuentas existentes.
    |
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
            /*
            |--------------------------------------------------------------------------
            | Cuenta pendiente de verificación
            |--------------------------------------------------------------------------
            |
            | Cuando el correo todavía no está verificado, se genera un nuevo
            | código y se conserva el correo en sesión para continuar el proceso.
            |
            */

            if (! $usuario->correoEstaVerificado()) {
                $this->procesarCodigoPendiente(
                    $usuario,
                    $request
                );
            } else {
                /*
                |--------------------------------------------------------------------------
                | Cuenta verificada
                |--------------------------------------------------------------------------
                |
                | Genera un token de acceso de un solo uso y coloca en la cola el
                | correo que contiene el enlace mágico.
                |
                */

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
            |--------------------------------------------------------------------------
            | Ocultar detalles internos
            |--------------------------------------------------------------------------
            |
            | No se devuelve el detalle al cliente para impedir que la respuesta
            | permita distinguir cuentas existentes de cuentas inexistentes.
            |
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
    |
    | Valida y consume el token recibido, inicia la sesión del usuario y lo
    | redirige a la página originalmente solicitada o al dashboard.
    |
    */

    public function magicLogin(
        string $token,
        Request $request
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Iniciar sesión mediante token
        |--------------------------------------------------------------------------
        |
        | El servicio valida y consume el token, autentica al usuario y regenera
        | la sesión para evitar reutilización del identificador anterior.
        |
        */

        $this->tokens
            ->iniciarSesionConToken(
                $token,
                $request
            );

        /*
        |--------------------------------------------------------------------------
        | Redirección posterior al acceso
        |--------------------------------------------------------------------------
        |
        | Si el middleware auth guardó una página pendiente, el usuario será
        | enviado a ella. En caso contrario, se utiliza el dashboard.
        |
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
    |
    | Presenta la vista utilizada para crear una nueva cuenta del Portal TI.
    |
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
    |
    | Crea una cuenta con el rol Usuario, genera el código de verificación y
    | coloca el correo correspondiente en la cola de entrega.
    |
    */

    public function store(
        RegistroRequest $request
    ): JsonResponse|RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Resolver rol predeterminado
        |--------------------------------------------------------------------------
        |
        | Todo registro nuevo se asocia al rol Usuario. Si el rol no existe,
        | se interrumpe el proceso para evitar crear una cuenta inconsistente.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Crear cuenta pendiente de verificación
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Conservar correo en sesión
        |--------------------------------------------------------------------------
        |
        | Permite identificar la cuenta pendiente durante la pantalla de
        | verificación y los posibles reenvíos de código.
        |
        */

        $request->session()->put(
            'correo_verificacion',
            $usuario->correo
        );

        try {
            /*
            |--------------------------------------------------------------------------
            | Generar y enviar código
            |--------------------------------------------------------------------------
            */

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
            /*
            |--------------------------------------------------------------------------
            | Cuenta creada, correo no procesado
            |--------------------------------------------------------------------------
            |
            | La creación de la cuenta no se revierte. Se informa al usuario que
            | debe solicitar un nuevo código desde la pantalla de verificación.
            |
            */

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

        /*
        |--------------------------------------------------------------------------
        | Respuesta según estado de entrega
        |--------------------------------------------------------------------------
        */

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
    |
    | Presenta la pantalla para introducir el código únicamente cuando existe
    | un correo pendiente de verificación almacenado en la sesión.
    |
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
    |
    | Valida el código asociado al correo, completa la verificación de la cuenta
    | y elimina de sesión el dato temporal utilizado durante el proceso.
    |
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
        |--------------------------------------------------------------------------
        | Limpiar correo pendiente
        |--------------------------------------------------------------------------
        |
        | El correo ya fue verificado y no es necesario conservarlo en sesión.
        |
        */

        $request
            ->session()
            ->forget(
                'correo_verificacion'
            );

        /*
        |--------------------------------------------------------------------------
        | Continuar al dashboard
        |--------------------------------------------------------------------------
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
    |
    | Genera un nuevo código para cuentas activas pendientes de verificación,
    | conserva una respuesta uniforme y registra el nuevo envío de correo.
    |
    */

    public function resendCode(
        Request $request
    ): JsonResponse|RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Resolver correo pendiente
        |--------------------------------------------------------------------------
        |
        | Se utiliza primero el valor enviado por la solicitud y, como respaldo,
        | el correo almacenado durante el registro.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Respuesta uniforme para cuentas no elegibles
        |--------------------------------------------------------------------------
        |
        | No se revela si la cuenta no existe, está inactiva o ya se encuentra
        | verificada.
        |
        */

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
            /*
            |--------------------------------------------------------------------------
            | Generar nuevo código
            |--------------------------------------------------------------------------
            */

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

        /*
        |--------------------------------------------------------------------------
        | Responder estado del nuevo envío
        |--------------------------------------------------------------------------
        */

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
    |
    | Finaliza la autenticación actual, invalida completamente la sesión y
    | genera un nuevo token CSRF antes de regresar a la pantalla de acceso.
    |
    */

    public function logout(
        Request $request
    ): RedirectResponse {
        Auth::logout();

        /*
        |--------------------------------------------------------------------------
        | Invalidar sesión
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->invalidate();

        /*
        |--------------------------------------------------------------------------
        | Regenerar token CSRF
        |--------------------------------------------------------------------------
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
    |
    | Genera un código de registro para una cuenta no verificada y coloca el
    | correo correspondiente en la cola de entrega.
    |
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
    |
    | Devuelve siempre el mismo mensaje para solicitudes de autenticación y
    | desactiva almacenamiento en caché de la respuesta.
    |
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
    |
    | Permite consultar únicamente el EmailDelivery autorizado en la sesión
    | actual y devuelve su estado de envío en una estructura JSON uniforme.
    |
    */

    public function emailStatus(
        Request $request,
        EmailDelivery $emailDelivery
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Validar delivery autorizado
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Responder estado actual
        |--------------------------------------------------------------------------
        */

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
    |
    | Conserva el identificador del último envío de autenticación para limitar
    | las consultas de estado al proceso perteneciente a la sesión actual.
    |
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
    |
    | Convierte un EmailDelivery en la estructura estándar utilizada por las
    | respuestas AJAX del flujo de registro y reenvío.
    |
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
