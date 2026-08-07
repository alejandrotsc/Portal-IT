<?php

namespace App\Services\Auth;

use App\Models\TokenAutenticacion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TokenAutenticacionService
{
    /*
    |--------------------------------------------------------------------------
    | Configuración
    |--------------------------------------------------------------------------
    |
    | Define los valores utilizados para controlar la expiración, cantidad
    | máxima de intentos y generación segura de códigos y tokens temporales
    | empleados durante los procesos de autenticación.
    |
    */

    private const MINUTOS_EXPIRACION = 5;

    private const MAXIMO_INTENTOS_CODIGO = 5;

    private const LONGITUD_CODIGO = 6;

    private const CODIGO_MINIMO = 0;

    private const CODIGO_MAXIMO = 999999;

    /*
    | 32 bytes equivalen a 256 bits.
    | bin2hex() los representa mediante 64 caracteres hexadecimales.
    */

    private const BYTES_TOKEN_LOGIN = 32;

    /*
    |--------------------------------------------------------------------------
    | Generar código de verificación
    |--------------------------------------------------------------------------
    |
    | Genera un código temporal de seis dígitos para verificar el correo
    | del usuario, invalida códigos anteriores y almacena únicamente
    | una representación segura del código generado.
    |
    */

    public function generarCodigoRegistro(
        Usuario $usuario
    ): string {
        return DB::transaction(function () use (
            $usuario
        ): string {
            /*
            | Bloquear al usuario evita que dos solicitudes simultáneas
            | generen dos códigos activos.
            */

            $usuarioBloqueado = Usuario::query()
                ->whereKey($usuario->id)
                ->lockForUpdate()
                ->first();

            $this->validarUsuario(
                $usuarioBloqueado
            );

            $correo = $this->normalizarCorreo(
                $usuarioBloqueado->correo
            );

            /*
            | Todo código de registro anterior queda inutilizado.
            */

            $this->invalidarTokensAnteriores(
                $correo,
                TokenAutenticacion::TIPO_REGISTRO
            );

            $codigo = $this->generarCodigoSeisDigitos();

            /*
            | El código original nunca se almacena.
            | Hash::make() genera un hash seguro con salt.
            */

            TokenAutenticacion::create([
                'usuario_id' => $usuarioBloqueado->id,

                'correo' => $correo,

                'token_hash' => Hash::make(
                    $codigo
                ),

                'tipo' =>
                    TokenAutenticacion::TIPO_REGISTRO,

                'expires_at' => now()->addMinutes(
                    self::MINUTOS_EXPIRACION
                ),

                'used_at' => null,

                'attempts' => 0,
            ]);

            /*
            | Solamente se devuelve para enviarlo por correo.
            */

            return $codigo;
        }, 3);
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar código de registro
    |--------------------------------------------------------------------------
    |
    | Valida el código proporcionado por el usuario, controla su vigencia
    | y cantidad de intentos, consume el token cuando corresponde y crea
    | la sesión una vez completada correctamente la verificación.
    |
    */

    public function verificarCodigoRegistro(
        string $correo,
        string $codigo,
        Request $request
    ): Usuario {
        $correo = $this->normalizarCorreo(
            $correo
        );

        $codigo = $this->normalizarCodigo(
            $codigo
        );

        /*
        | El código debe conservarse como string para permitir valores
        | como 000042.
        */

        if (
            ! preg_match(
                '/^\d{' . self::LONGITUD_CODIGO . '}$/',
                $codigo
            )
        ) {
            throw ValidationException::withMessages([
                'codigo' =>
                    'El código debe contener exactamente 6 dígitos.',
            ]);
        }

        /*
        | No se lanzan excepciones dentro de la transacción después
        | de modificar el token, porque Laravel revertiría el incremento
        | de intentos. En su lugar, se devuelve el error y se lanza después.
        */

        $resultado = DB::transaction(function () use (
            $correo,
            $codigo
        ): array {
            /*
            | Se mantiene siempre el mismo orden de bloqueos:
            |
            | 1. Usuario
            | 2. Token
            |
            | Esto reduce el riesgo de bloqueos cruzados.
            */

            $usuario = Usuario::query()
                ->where('correo', $correo)
                ->lockForUpdate()
                ->first();

            if (! $usuario) {
                return [
                    'usuario' => null,
                    'error' =>
                        'No existe un código de verificación activo.',
                ];
            }

            if (! $usuario->activo) {
                return [
                    'usuario' => null,
                    'error' =>
                        'Esta cuenta se encuentra desactivada.',
                ];
            }

            /*
            | lockForUpdate() garantiza que solo una petición pueda
            | procesar este código a la vez.
            */

            $token = TokenAutenticacion::query()
                ->where('usuario_id', $usuario->id)
                ->where('correo', $correo)
                ->where(
                    'tipo',
                    TokenAutenticacion::TIPO_REGISTRO
                )
                ->whereNull('used_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $token) {
                return [
                    'usuario' => null,
                    'error' =>
                        'No existe un código de verificación activo.',
                ];
            }

            /*
            | Invalidar si ya venció.
            */

            if ($token->estaVencido()) {
                $this->consumirToken(
                    $token
                );

                return [
                    'usuario' => null,
                    'error' =>
                        'El código ha vencido. Solicita uno nuevo.',
                ];
            }

            /*
            | Protección adicional si el registro ya alcanzó
            | el límite de intentos.
            */

            if (
                $token->attempts
                >= self::MAXIMO_INTENTOS_CODIGO
            ) {
                $this->consumirToken(
                    $token
                );

                return [
                    'usuario' => null,
                    'error' =>
                        'El código fue bloqueado por demasiados intentos. Solicita uno nuevo.',
                ];
            }

            /*
            | Comparar el código recibido con el hash almacenado.
            */

            if (
                ! Hash::check(
                    $codigo,
                    $token->token_hash
                )
            ) {
                $intentos = $token->attempts + 1;

                $datosActualizacion = [
                    'attempts' => $intentos,
                    'updated_at' => now(),
                ];

                /*
                | El quinto intento fallido consume inmediatamente
                | el código.
                */

                if (
                    $intentos
                    >= self::MAXIMO_INTENTOS_CODIGO
                ) {
                    $datosActualizacion['used_at'] = now();
                }

                $token->update(
                    $datosActualizacion
                );

                if (
                    $intentos
                    >= self::MAXIMO_INTENTOS_CODIGO
                ) {
                    return [
                        'usuario' => null,
                        'error' =>
                            'El código fue bloqueado por demasiados intentos. Solicita uno nuevo.',
                    ];
                }

                $intentosRestantes =
                    self::MAXIMO_INTENTOS_CODIGO
                    - $intentos;

                return [
                    'usuario' => null,
                    'error' =>
                        'El código ingresado no es válido. Intentos restantes: '
                        . $intentosRestantes
                        . '.',
                ];
            }

            /*
            | Consumir el código dentro de la misma transacción.
            */

            $this->consumirToken(
                $token
            );

            /*
            | Marcar el correo como verificado.
            */

            if (! $usuario->correoEstaVerificado()) {
                $usuario->marcarCorreoComoVerificado();
            }

            return [
                'usuario' => $usuario->fresh(),
                'error' => null,
            ];
        }, 3);

        if ($resultado['error'] !== null) {
            throw ValidationException::withMessages([
                'codigo' => $resultado['error'],
            ]);
        }

        /** @var Usuario $usuario */
        $usuario = $resultado['usuario'];

        $this->crearSesion(
            $usuario,
            $request
        );

        return $usuario;
    }

    /*
    |--------------------------------------------------------------------------
    | Generar token para enlace mágico
    |--------------------------------------------------------------------------
    |
    | Genera un token criptográficamente seguro para permitir el inicio
    | de sesión mediante enlace mágico, invalida enlaces anteriores y
    | almacena únicamente el hash SHA-256 del token original.
    |
    */

    public function generarTokenLogin(
        Usuario $usuario
    ): string {
        return DB::transaction(function () use (
            $usuario
        ): string {
            /*
            | Bloquear el usuario evita generar dos enlaces activos
            | en solicitudes simultáneas.
            */

            $usuarioBloqueado = Usuario::query()
                ->whereKey($usuario->id)
                ->lockForUpdate()
                ->first();

            $this->validarUsuario(
                $usuarioBloqueado
            );

            if (
                ! $usuarioBloqueado
                    ->correoEstaVerificado()
            ) {
                throw ValidationException::withMessages([
                    'correo' =>
                        'Debes verificar tu correo antes de iniciar sesión.',
                ]);
            }

            $correo = $this->normalizarCorreo(
                $usuarioBloqueado->correo
            );

            /*
            | Todo enlace de login anterior queda inutilizado.
            */

            $this->invalidarTokensAnteriores(
                $correo,
                TokenAutenticacion::TIPO_LOGIN
            );

            /*
            | Generar 32 bytes criptográficamente seguros.
            |
            | Resultado:
            | 256 bits de entropía.
            | 64 caracteres hexadecimales.
            */

            $tokenPlano = bin2hex(
                random_bytes(
                    self::BYTES_TOKEN_LOGIN
                )
            );

            /*
            | Se almacena únicamente SHA-256.
            |
            | Para tokens aleatorios de alta entropía es apropiado usar
            | un hash determinista, porque necesitamos buscarlo directamente.
            */

            TokenAutenticacion::create([
                'usuario_id' => $usuarioBloqueado->id,

                'correo' => $correo,

                'token_hash' => hash(
                    'sha256',
                    $tokenPlano
                ),

                'tipo' =>
                    TokenAutenticacion::TIPO_LOGIN,

                'expires_at' => now()->addMinutes(
                    self::MINUTOS_EXPIRACION
                ),

                'used_at' => null,

                /*
                | Los intentos no se utilizan para el enlace mágico.
                | Las solicitudes inválidas se controlan con throttle
                | en las rutas.
                */

                'attempts' => 0,
            ]);

            /*
            | El token original solamente se utiliza en el enlace.
            */

            return $tokenPlano;
        }, 3);
    }

    /*
    |--------------------------------------------------------------------------
    | Iniciar sesión con enlace mágico
    |--------------------------------------------------------------------------
    |
    | Valida el token recibido desde el enlace mágico, comprueba que siga
    | vigente y no haya sido utilizado, lo consume de forma segura y crea
    | la sesión autenticada correspondiente al usuario.
    |
    */

    public function iniciarSesionConToken(
        string $tokenPlano,
        Request $request
    ): Usuario {
        $tokenPlano = $this->normalizarTokenLogin(
            $tokenPlano
        );

        /*
        | Aceptar únicamente 64 caracteres hexadecimales.
        */

        if (
            ! preg_match(
                '/^[a-f0-9]{64}$/',
                $tokenPlano
            )
        ) {
            throw ValidationException::withMessages([
                'token' =>
                    'El enlace de acceso no es válido o ya fue utilizado.',
            ]);
        }

        $tokenHash = hash(
            'sha256',
            $tokenPlano
        );

        /*
        | Primera búsqueda sin bloqueo para localizar el usuario.
        |
        | La comprobación definitiva se realiza nuevamente dentro
        | de la transacción.
        */

        $candidato = TokenAutenticacion::query()
            ->select([
                'id',
                'usuario_id',
            ])
            ->where(
                'token_hash',
                $tokenHash
            )
            ->where(
                'tipo',
                TokenAutenticacion::TIPO_LOGIN
            )
            ->first();

        if (! $candidato) {
            throw ValidationException::withMessages([
                'token' =>
                    'El enlace de acceso no es válido o ya fue utilizado.',
            ]);
        }

        $resultado = DB::transaction(function () use (
            $candidato,
            $tokenHash
        ): array {
            /*
            | Mantener el mismo orden de bloqueo:
            |
            | 1. Usuario
            | 2. Token
            */

            $usuario = Usuario::query()
                ->whereKey($candidato->usuario_id)
                ->lockForUpdate()
                ->first();

            if (! $usuario || ! $usuario->activo) {
                return [
                    'usuario' => null,
                    'error' =>
                        'El enlace de acceso no es válido o la cuenta no está disponible.',
                ];
            }

            /*
            | Volver a consultar y bloquear el token.
            | Aquí se comprueba definitivamente si ya fue usado.
            */

            $token = TokenAutenticacion::query()
                ->whereKey($candidato->id)
                ->where(
                    'token_hash',
                    $tokenHash
                )
                ->where(
                    'tipo',
                    TokenAutenticacion::TIPO_LOGIN
                )
                ->lockForUpdate()
                ->first();

            if (
                ! $token
                || $token->used_at !== null
            ) {
                return [
                    'usuario' => null,
                    'error' =>
                        'El enlace de acceso no es válido o ya fue utilizado.',
                ];
            }

            /*
            | Invalidar si venció.
            */

            if ($token->estaVencido()) {
                $this->consumirToken(
                    $token
                );

                return [
                    'usuario' => null,
                    'error' =>
                        'El enlace de acceso ha vencido. Solicita uno nuevo.',
                ];
            }

            if (
                ! $usuario
                    ->correoEstaVerificado()
            ) {
                /*
                | También se consume para que el enlace no quede activo.
                */

                $this->consumirToken(
                    $token
                );

                return [
                    'usuario' => null,
                    'error' =>
                        'El correo todavía no ha sido verificado.',
                ];
            }

            /*
            | El bloqueo y el consumo suceden dentro de la misma
            | transacción. Dos navegadores no pueden consumirlo.
            */

            $this->consumirToken(
                $token
            );

            return [
                'usuario' => $usuario,
                'error' => null,
            ];
        }, 3);

        if ($resultado['error'] !== null) {
            throw ValidationException::withMessages([
                'token' => $resultado['error'],
            ]);
        }

        /** @var Usuario $usuario */
        $usuario = $resultado['usuario'];

        $this->crearSesion(
            $usuario,
            $request
        );

        return $usuario;
    }

    /*
    |--------------------------------------------------------------------------
    | Generar código de seis dígitos
    |--------------------------------------------------------------------------
    |
    | Genera un número aleatorio dentro del rango permitido y lo completa
    | con ceros a la izquierda para garantizar una longitud de seis dígitos.
    |
    */

    private function generarCodigoSeisDigitos(): string
    {
        $numero = random_int(
            self::CODIGO_MINIMO,
            self::CODIGO_MAXIMO
        );

        /*
        | El resultado siempre tiene seis caracteres:
        |
        | 0      -> 000000
        | 42     -> 000042
        | 38291  -> 038291
        | 999999 -> 999999
        */

        return str_pad(
            (string) $numero,
            self::LONGITUD_CODIGO,
            '0',
            STR_PAD_LEFT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Consumir token o código
    |--------------------------------------------------------------------------
    |
    | Marca la credencial temporal como utilizada para impedir que pueda
    | volver a emplearse en un proceso posterior de autenticación.
    |
    */

    private function consumirToken(
        TokenAutenticacion $token
    ): void {
        $token->update([
            'used_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear sesión de Laravel
    |--------------------------------------------------------------------------
    |
    | Autentica al usuario mediante el sistema de sesiones de Laravel y
    | regenera el identificador de sesión para evitar ataques de fijación.
    |
    */

    private function crearSesion(
        Usuario $usuario,
        Request $request
    ): void {
        /*
        | El token solamente concede acceso una vez.
        | A partir de aquí Laravel autentica mediante la sesión.
        */

        Auth::login($usuario);

        /*
        | Generar un identificador nuevo evita ataques
        | de fijación de sesión.
        */

        $request
            ->session()
            ->regenerate();
    }

    /*
    |--------------------------------------------------------------------------
    | Invalidar credenciales anteriores
    |--------------------------------------------------------------------------
    |
    | Marca como utilizadas las credenciales activas anteriores del mismo
    | correo y tipo para garantizar que solamente permanezca vigente la
    | credencial temporal generada más recientemente.
    |
    */

    private function invalidarTokensAnteriores(
        string $correo,
        string $tipo
    ): void {
        TokenAutenticacion::query()
            ->where('correo', $correo)
            ->where('tipo', $tipo)
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validar usuario
    |--------------------------------------------------------------------------
    |
    | Comprueba que el usuario exista y que su cuenta se encuentre activa
    | antes de permitir la generación o utilización de credenciales.
    |
    */

    private function validarUsuario(
        ?Usuario $usuario
    ): void {
        if (! $usuario) {
            throw ValidationException::withMessages([
                'correo' =>
                    'El usuario asociado no existe.',
            ]);
        }

        if (! $usuario->activo) {
            throw ValidationException::withMessages([
                'correo' =>
                    'Esta cuenta se encuentra desactivada.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar correo
    |--------------------------------------------------------------------------
    |
    | Elimina espacios externos y convierte el correo a minúsculas para
    | mantener un formato consistente durante búsquedas y validaciones.
    |
    */

    private function normalizarCorreo(
        string $correo
    ): string {
        return mb_strtolower(
            trim($correo)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar código
    |--------------------------------------------------------------------------
    |
    | Elimina únicamente los espacios ubicados al inicio y al final del
    | código. No modifica letras, símbolos ni otros caracteres internos
    | para que la validación posterior pueda detectar entradas inválidas.
    |
    */

    private function normalizarCodigo(
        string $codigo
    ): string {
        return trim(
            $codigo
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar token del enlace
    |--------------------------------------------------------------------------
    |
    | Elimina espacios externos y convierte el token hexadecimal a
    | minúsculas antes de realizar su validación y búsqueda.
    |
    */

    private function normalizarTokenLogin(
        string $tokenPlano
    ): string {
        return strtolower(
            trim($tokenPlano)
        );
    }
}