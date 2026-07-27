<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class OcrService
{
    /**
     * Lee una imagen mediante Tesseract y devuelve
     * el código o mensaje de error identificado.
     *
     * Siempre retorna string para mantener compatibilidad
     * con el registro de incidencias y el envío del correo.
     */
    public function leerImagen(string $ruta): string
    {
        if (!is_file($ruta)) {
            Log::warning('OCR: La imagen no existe.', [
                'ruta' => $ruta,
            ]);

            return '';
        }

        try {
            $process = new Process([
                config(
                    'services.tesseract.path',
                    env('TESSERACT_PATH', 'tesseract')
                ),
                $ruta,
                'stdout',
                '-l',
                'spa+eng',
                '--psm',
                '11',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Tiempo máximo de ejecución
            |--------------------------------------------------------------------------
            */

            $process->setTimeout(30);

            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('OCR: Tesseract no pudo procesar la imagen.', [
                    'ruta' => $ruta,
                    'error' => trim(
                        $process->getErrorOutput()
                    ),
                ]);

                /*
                 * Un error del OCR nunca debe detener
                 * el registro ni el envío del correo.
                 */
                return '';
            }

            $textoOriginal = trim(
                $process->getOutput()
            );

            Log::info('OCR: Texto original identificado.', [
                'ruta' => $ruta,
                'texto' => $textoOriginal,
            ]);

            if ($textoOriginal === '') {
                Log::info('OCR: Tesseract no encontró texto.', [
                    'ruta' => $ruta,
                ]);

                return '';
            }

            $textoNormalizado = $this->normalizarTexto(
                $textoOriginal
            );

            $resultado = $this->extraerError(
                $textoNormalizado
            );

            Log::info('OCR: Resultado final.', [
                'ruta' => $ruta,
                'resultado' => $resultado,
            ]);

            return $resultado;
        } catch (\Throwable $e) {
            Log::error('OCR: Error inesperado.', [
                'ruta' => $ruta,
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ]);

            /*
             * El OCR es complementario.
             * La incidencia debe continuar registrándose.
             */
            return '';
        }
    }

    /**
     * Busca primero códigos de error, luego mensajes
     * relacionados con fallos y finalmente utiliza
     * el texto reconocido como respaldo.
     */
    private function extraerError(string $texto): string
    {
        $texto = trim($texto);

        if ($texto === '') {
            return '';
        }

        $lineas = preg_split(
            '/\R/u',
            $texto
        );

        if (!is_array($lineas)) {
            return '';
        }

        $lineasUtiles = [];

        foreach ($lineas as $linea) {
            $linea = $this->limpiarLinea(
                $linea
            );

            if ($linea === '') {
                continue;
            }

            if ($this->esLineaBasura($linea)) {
                continue;
            }

            $lineasUtiles[] = $linea;
        }

        $lineasUtiles = array_values(
            array_unique($lineasUtiles)
        );

        if (empty($lineasUtiles)) {
            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Buscar códigos de error
        |--------------------------------------------------------------------------
        */

        $lineasConCodigo = [];

        foreach ($lineasUtiles as $indice => $linea) {
            if (!$this->contieneCodigoError($linea)) {
                continue;
            }

            /*
             * Agregar una línea anterior cuando pueda contener
             * la descripción del error.
             */
            if (
                isset($lineasUtiles[$indice - 1]) &&
                $this->esLineaDescriptiva(
                    $lineasUtiles[$indice - 1]
                )
            ) {
                $lineasConCodigo[] =
                    $lineasUtiles[$indice - 1];
            }

            $lineasConCodigo[] = $linea;

            /*
             * Agregar la línea siguiente cuando pueda contener
             * una explicación relacionada.
             */
            if (
                isset($lineasUtiles[$indice + 1]) &&
                $this->esLineaDescriptiva(
                    $lineasUtiles[$indice + 1]
                )
            ) {
                $lineasConCodigo[] =
                    $lineasUtiles[$indice + 1];
            }
        }

        $lineasConCodigo = array_values(
            array_unique($lineasConCodigo)
        );

        if (!empty($lineasConCodigo)) {
            return $this->limitarResultado(
                implode("\n", $lineasConCodigo)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Buscar mensajes de error sin código
        |--------------------------------------------------------------------------
        */

        $mensajesError = [];

        foreach ($lineasUtiles as $indice => $linea) {
            if (!$this->pareceMensajeError($linea)) {
                continue;
            }

            $mensajesError[] = $linea;

            /*
             * Cuando la siguiente línea parece una explicación,
             * también se agrega.
             */
            if (
                isset($lineasUtiles[$indice + 1]) &&
                $this->esLineaDescriptiva(
                    $lineasUtiles[$indice + 1]
                )
            ) {
                $mensajesError[] =
                    $lineasUtiles[$indice + 1];
            }
        }

        $mensajesError = array_values(
            array_unique($mensajesError)
        );

        if (!empty($mensajesError)) {
            return $this->limitarResultado(
                implode("\n", $mensajesError)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Respaldo
        |--------------------------------------------------------------------------
        |
        | Si Tesseract identificó texto, pero no coincide con los patrones,
        | se devuelven las primeras líneas útiles para evitar que aparezca vacío.
        |
        */

        $respaldo = array_slice(
            $lineasUtiles,
            0,
            8
        );

        return $this->limitarResultado(
            implode("\n", $respaldo)
        );
    }

    /**
     * Determina si una línea contiene un código
     * de error conocido.
     */
    private function contieneCodigoError(string $linea): bool
    {
        foreach ($this->patronesCodigosError() as $patron) {
            if (preg_match($patron, $linea) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Patrones de códigos de error comunes.
     */
    private function patronesCodigosError(): array
    {
        return [
            /*
             * Windows:
             * 0x80070005
             * 0xc000007b
             */
            '/\b0[xX][0-9A-Fa-f]{6,16}\b/u',

            /*
             * HRESULT:
             * HRESULT: 0x80004005
             * HRESULT 80070005
             */
            '/\bHRESULT\s*[:#-]?\s*(?:0[xX])?[0-9A-Fa-f]{8}\b/iu',

            /*
             * Códigos HTTP:
             * HTTP 404
             * HTTP/1.1 500
             * Código HTTP: 403
             */
            '/\bHTTP(?:\/\d(?:\.\d)?)?\s*[:#-]?\s*[1-5]\d{2}\b/iu',

            '/\b(?:Status\s+Code|Código\s+HTTP|Codigo\s+HTTP)\s*[:#-]?\s*[1-5]\d{2}\b/iu',

            /*
             * Error 404, Error 500, etc.
             */
            '/\bError\s*[:#-]?\s*(?:400|401|402|403|404|405|406|407|408|409|410|411|412|413|414|415|416|417|418|422|423|424|425|426|428|429|431|451|500|501|502|503|504|505|506|507|508|510|511)\b/iu',

            /*
             * PostgreSQL y otras bases de datos:
             * SQLSTATE[23505]
             * SQLSTATE: 42P01
             */
            '/\bSQLSTATE\s*(?:\[|:)?\s*[0-9A-Z]{5}\]?/iu',

            /*
             * Oracle:
             * ORA-00942
             */
            '/\bORA-\d{5}\b/iu',

            /*
             * MySQL:
             * MySQL Error 1062
             * Error Code: 1045
             */
            '/\b(?:MySQL\s+Error|Error\s+Code)\s*[:#-]?\s*\d{3,5}\b/iu',

            /*
             * SQL Server:
             * SQL Server Error 18456
             * Msg 208
             */
            '/\b(?:SQL\s+Server\s+Error|Msg)\s*[:#-]?\s*\d{1,6}\b/iu',

            /*
             * cURL:
             * cURL error 28
             */
            '/\bcURL\s+(?:error|code)\s*[:#-]?\s*\d{1,3}\b/iu',

            /*
             * Navegadores:
             * ERR_CONNECTION_REFUSED
             */
            '/\bERR_[A-Z0-9_]{3,60}\b/u',

            /*
             * Microsoft COM:
             * E_ACCESSDENIED
             * E_INVALIDARG
             */
            '/\bE_[A-Z0-9_]{2,60}\b/u',

            /*
             * Excepciones comunes de Laravel/PHP.
             */
            '/\b(?:QueryException|ErrorException|ValidationException|AuthenticationException|AuthorizationException|ModelNotFoundException|NotFoundHttpException|MethodNotAllowedHttpException)\b/u',

            /*
             * Códigos explícitos:
             * Código de error: ABC-123
             * Error Code: 1001
             */
            '/\b(?:Código\s+de\s+error|Codigo\s+de\s+error|Error\s+Code)\s*[:#-]?\s*[A-Z0-9][A-Z0-9_.-]{2,40}\b/iu',
        ];
    }

    /**
     * Determina si una línea parece contener
     * un mensaje de error aunque no tenga código.
     */
    private function pareceMensajeError(string $linea): bool
    {
        foreach ($this->patronesMensajesError() as $patron) {
            if (preg_match($patron, $linea) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Patrones de mensajes de error en español e inglés.
     */
    private function patronesMensajesError(): array
    {
        return [
            '/\berror\b/iu',
            '/\bexception\b/iu',
            '/\badvertencia\b/iu',
            '/\bwarning\b/iu',
            '/\bfatal\b/iu',
            '/\bfalla\b/iu',
            '/\bfall[oó]\b/iu',
            '/\bproblema\b/iu',
            '/\bdenegad[oa]\b/iu',

            '/\bno se pudo\b/iu',
            '/\bno se puede\b/iu',
            '/\bno fue posible\b/iu',
            '/\bno se encuentra\b/iu',
            '/\bno encontrado\b/iu',
            '/\bno disponible\b/iu',
            '/\bno responde\b/iu',
            '/\bno existe\b/iu',
            '/\bno tiene permisos\b/iu',
            '/\bno tiene acceso\b/iu',
            '/\bno se reconoce\b/iu',
            '/\bno se pudo completar\b/iu',
            '/\bno se pudo iniciar\b/iu',
            '/\bno se pudo conectar\b/iu',

            '/\bsin conexi[oó]n\b/iu',
            '/\bconexi[oó]n rechazada\b/iu',
            '/\bconexi[oó]n interrumpida\b/iu',
            '/\btiempo de espera\b/iu',
            '/\bacceso denegado\b/iu',
            '/\barchivo dañado\b/iu',
            '/\barchivo no encontrado\b/iu',
            '/\bpágina no encontrada\b/iu',
            '/\bpagina no encontrada\b/iu',

            '/\bfailed\b/iu',
            '/\bfailure\b/iu',
            '/\baccess denied\b/iu',
            '/\bpermission denied\b/iu',
            '/\bnot found\b/iu',
            '/\bunavailable\b/iu',
            '/\bcannot\b/iu',
            '/\bunable to\b/iu',
            '/\bconnection refused\b/iu',
            '/\bconnection failed\b/iu',
            '/\btimeout\b/iu',
            '/\btimed out\b/iu',
        ];
    }

    /**
     * Normaliza el texto producido por Tesseract.
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = str_replace(
            ["\r\n", "\r"],
            "\n",
            $texto
        );

        /*
        |--------------------------------------------------------------------------
        | Correcciones frecuentes del OCR
        |--------------------------------------------------------------------------
        */

        /*
         * Ox80070005 u OX80070005
         * se convierte en 0x80070005.
         */
        $texto = preg_replace(
            '/\b[Oo][xX](?=[0-9A-Fa-f]{6,16}\b)/u',
            '0x',
            $texto
        );

        /*
         * 0 x 80070005 o 0x 80070005
         * se convierte en 0x80070005.
         */
        $texto = preg_replace(
            '/\b0\s*[xX]\s*([0-9A-Fa-f]{6,16})\b/u',
            '0x$1',
            $texto
        );

        /*
         * SQL STATE -> SQLSTATE
         */
        $texto = preg_replace(
            '/\bSQL\s+STATE\b/iu',
            'SQLSTATE',
            $texto
        );

        /*
         * ORA - 00942 -> ORA-00942
         */
        $texto = preg_replace(
            '/\bORA\s*-\s*(\d{5})\b/iu',
            'ORA-$1',
            $texto
        );

        /*
         * ERR CONNECTION REFUSED
         * -> ERR_CONNECTION_REFUSED
         */
        $texto = preg_replace_callback(
            '/\bERR(?:\s+[A-Z0-9]+){1,8}\b/u',
            static function (array $coincidencia): string {
                return preg_replace(
                    '/\s+/',
                    '_',
                    $coincidencia[0]
                );
            },
            $texto
        );

        /*
        |--------------------------------------------------------------------------
        | Eliminar emojis e iconos
        |--------------------------------------------------------------------------
        */

        $texto = preg_replace(
            '/[\x{1F000}-\x{1FAFF}]/u',
            '',
            $texto
        );

        /*
        |--------------------------------------------------------------------------
        | Mantener caracteres legibles
        |--------------------------------------------------------------------------
        */

        $texto = preg_replace(
            '/[^\p{L}\p{N}\p{P}\p{Z}\n]/u',
            ' ',
            $texto
        );

        return trim($texto);
    }

    /**
     * Limpia una línea sin eliminar códigos,
     * signos o información importante.
     */
    private function limpiarLinea(?string $linea): string
    {
        if (!$linea) {
            return '';
        }

        $linea = trim($linea);

        /*
         * Quitar caracteres basura al inicio.
         *
         * Ejemplos:
         * | Error 404
         * — Acceso denegado
         * • No se pudo conectar
         */
        $linea = preg_replace(
            '/^[|;:—–•·]+\s*/u',
            '',
            $linea
        );

        /*
         * Reducir espacios y tabulaciones.
         */
        $linea = preg_replace(
            '/[ \t]{2,}/u',
            ' ',
            $linea
        );

        /*
         * Ignorar líneas compuestas solamente
         * por símbolos.
         */
        if (!preg_match('/[\p{L}\p{N}]/u', $linea)) {
            return '';
        }

        return trim($linea);
    }

    /**
     * Elimina líneas propias de la interfaz
     * que no aportan al diagnóstico.
     */
    private function esLineaBasura(string $linea): bool
    {
        $lineasBasura = [
            'Texto identificado automáticamente',
            'Texto identificado automaticamente',
            'Texto detectado automáticamente',
            'Texto detectado automaticamente',
            'Cerrar',
            'Cancelar',
            'Aceptar',
            'OK',
            'Aceptar y cerrar',
            'Más información',
            'Mas información',
            'Más detalles',
            'Mas detalles',
            'Copiar',
            'Volver',
            'Reintentar',
            'Intentar de nuevo',
            'error.jpg',
            'error.png',
            'captura.jpg',
            'captura.png',
        ];

        foreach ($lineasBasura as $basura) {
            if (mb_strtolower($linea) === mb_strtolower($basura)) {
                return true;
            }
        }

        /*
         * Nombres de archivos de imagen aislados.
         */
        if (
            preg_match(
                '/^[\w\s.-]+\.(?:jpg|jpeg|png|webp|bmp)$/iu',
                $linea
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determina si una línea cercana puede servir
     * como descripción del error.
     */
    private function esLineaDescriptiva(string $linea): bool
    {
        $longitud = mb_strlen($linea);

        if ($longitud < 4 || $longitud > 300) {
            return false;
        }

        if ($this->esLineaBasura($linea)) {
            return false;
        }

        /*
         * Debe contener al menos una palabra.
         */
        return preg_match(
            '/\p{L}{3,}/u',
            $linea
        ) === 1;
    }

    /**
     * Limita el resultado para evitar guardar
     * textos demasiado extensos.
     */
    private function limitarResultado(string $resultado): string
    {
        $resultado = trim($resultado);

        if ($resultado === '') {
            return '';
        }

        /*
         * Máximo 1,500 caracteres.
         */
        return trim(
            mb_substr(
                $resultado,
                0,
                1500
            )
        );
    }
}