<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class OcrService
{
    /*
    |--------------------------------------------------------------------------
    | Leer imagen mediante OCR
    |--------------------------------------------------------------------------
    |
    | Ejecuta Tesseract sobre la imagen recibida, normaliza el texto
    | reconocido y extrae códigos o mensajes de error útiles para el
    | registro de incidencias y el envío de correo.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Extraer error del texto reconocido
    |--------------------------------------------------------------------------
    |
    | Prioriza códigos de error, posteriormente mensajes relacionados con
    | fallos y finalmente utiliza las primeras líneas útiles como respaldo.
    |
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
        |
        | Busca primero patrones de códigos reconocibles y conserva líneas cercanas que puedan aportar contexto descriptivo.
        |
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
        | 2. Buscar mensajes de error o fallo
        |--------------------------------------------------------------------------
        |
        | Detecta expresiones relacionadas con errores en español e inglés
        | y conserva líneas cercanas que puedan aportar contexto adicional.
        |
        */

$mensajesError = [];

foreach ($lineasUtiles as $indice => $linea) {

    if (!$this->pareceMensajeError($linea)) {
        continue;
    }

    /*
     * Línea anterior:
     * puede contener el título de la ventana.
     */
    if (
        isset($lineasUtiles[$indice - 1]) &&
        $this->esLineaDescriptiva(
            $lineasUtiles[$indice - 1]
        )
    ) {
        $mensajesError[] =
            $lineasUtiles[$indice - 1];
    }

    /*
     * Línea donde se identificó el patrón.
     */
    $mensajesError[] = $linea;

    /*
     * Primera línea posterior:
     * generalmente contiene la explicación.
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

    /*
     * Segunda línea posterior:
     * puede contener código, causa o recomendación.
     */
    if (
        isset($lineasUtiles[$indice + 2]) &&
        (
            $this->esLineaDescriptiva(
                $lineasUtiles[$indice + 2]
            ) ||
            $this->contieneCodigoError(
                $lineasUtiles[$indice + 2]
            )
        )
    ) {
        $mensajesError[] =
            $lineasUtiles[$indice + 2];
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

    /*
    |--------------------------------------------------------------------------
    | Detectar código de error
    |--------------------------------------------------------------------------
    |
    | Comprueba si una línea coincide con alguno de los patrones de códigos
    | de error conocidos por el servicio.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Patrones de códigos de error
    |--------------------------------------------------------------------------
    |
    | Define expresiones regulares para reconocer códigos frecuentes de
    | Windows, HTTP, bases de datos, navegadores y otras plataformas.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Detectar mensaje de error
    |--------------------------------------------------------------------------
    |
    | Comprueba si una línea contiene expresiones asociadas a fallos,
    | advertencias, problemas de conexión, permisos u otros errores.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Patrones de mensajes de error
    |--------------------------------------------------------------------------
    |
    | Define expresiones comunes en español e inglés relacionadas con
    | fallos, permisos, autenticación, conectividad, archivos, instalación,
    | recursos y disponibilidad de servicios.
    |
    */
private function patronesMensajesError(): array
{
    return [

        /*
        |--------------------------------------------------------------------------
        | Palabras generales
        |--------------------------------------------------------------------------
        |
        | Agrupa términos generales utilizados habitualmente para describir errores, fallos, advertencias y problemas.
        |
        */

        '/\berror(?:es)?\b/iu',
        '/\bexception\b/iu',
        '/\bwarning\b/iu',
        '/\badvertencia\b/iu',
        '/\bfatal\b/iu',
        '/\bcritical\b/iu',
        '/\bcrítico\b/iu',
        '/\bcritico\b/iu',

        '/\bfalla(?:s)?\b/iu',
        '/\bfall[oó]\b/iu',
        '/\bfallando\b/iu',
        '/\bproblema(?:s)?\b/iu',

        '/\bfailed\b/iu',
        '/\bfailure\b/iu',
        '/\bfault\b/iu',
        '/\bproblem\b/iu',
        '/\bissue\b/iu',

        /*
        |--------------------------------------------------------------------------
        | Mensajes generales en español
        |--------------------------------------------------------------------------
        |
        | Incluye expresiones frecuentes utilizadas por aplicaciones y sistemas para comunicar fallos en español.
        |
        */

        '/\bno se pudo\b/iu',
        '/\bno se puede\b/iu',
        '/\bno fue posible\b/iu',
        '/\bno es posible\b/iu',
        '/\bno se logra\b/iu',
        '/\bno funciona\b/iu',
        '/\bdejó de funcionar\b/iu',
        '/\bdejo de funcionar\b/iu',
        '/\bha dejado de funcionar\b/iu',
        '/\bocurrió un problema\b/iu',
        '/\bocurrio un problema\b/iu',
        '/\balgo salió mal\b/iu',
        '/\balgo salio mal\b/iu',
        '/\berror inesperado\b/iu',
        '/\boperación fallida\b/iu',
        '/\boperacion fallida\b/iu',

        /*
        |--------------------------------------------------------------------------
        | Mensajes generales en inglés
        |--------------------------------------------------------------------------
        |
        | Incluye expresiones comunes utilizadas por aplicaciones y sistemas para comunicar fallos en inglés.
        |
        */

        '/\bsomething went wrong\b/iu',
        '/\ban error occurred\b/iu',
        '/\ban unexpected error occurred\b/iu',
        '/\bunexpected error\b/iu',
        '/\boperation failed\b/iu',
        '/\brequest failed\b/iu',
        '/\bprocess failed\b/iu',
        '/\bapplication failed\b/iu',
        '/\bapplication error\b/iu',
        '/\bservice failed\b/iu',

        '/\bcannot\b/iu',
        '/\bcan\'t\b/iu',
        '/\bcould not\b/iu',
        '/\bunable to\b/iu',
        '/\bnot able to\b/iu',
        '/\bwas not successful\b/iu',
        '/\bhas stopped working\b/iu',
        '/\bstopped working\b/iu',
        '/\bnot responding\b/iu',

        /*
        |--------------------------------------------------------------------------
        | Acceso, permisos y autenticación
        |--------------------------------------------------------------------------
        |
        | Detecta mensajes relacionados con permisos, credenciales, autenticación, sesiones y acceso restringido.
        |
        */

        '/\bacceso denegado\b/iu',
        '/\bpermiso denegado\b/iu',
        '/\bpermisos insuficientes\b/iu',
        '/\bno tiene permisos\b/iu',
        '/\bno tiene acceso\b/iu',
        '/\bno autorizado\b/iu',
        '/\bautenticación fallida\b/iu',
        '/\bautenticacion fallida\b/iu',
        '/\bcredenciales incorrectas\b/iu',
        '/\bcontraseña incorrecta\b/iu',
        '/\bcontrasena incorrecta\b/iu',
        '/\bsesión expirada\b/iu',
        '/\bsesion expirada\b/iu',

        '/\baccess denied\b/iu',
        '/\bpermission denied\b/iu',
        '/\binsufficient permissions\b/iu',
        '/\bunauthorized\b/iu',
        '/\bforbidden\b/iu',
        '/\bauthentication failed\b/iu',
        '/\blogin failed\b/iu',
        '/\binvalid credentials\b/iu',
        '/\bincorrect password\b/iu',
        '/\bsession expired\b/iu',
        '/\baccount locked\b/iu',

        /*
        |--------------------------------------------------------------------------
        | Conectividad y red
        |--------------------------------------------------------------------------
        |
        | Detecta fallos relacionados con conexión, disponibilidad de red, servidores, tiempo de espera y DNS.
        |
        */

        '/\bsin conexi[oó]n\b/iu',
        '/\bno hay conexi[oó]n\b/iu',
        '/\bno se pudo conectar\b/iu',
        '/\bno se puede conectar\b/iu',
        '/\bconexi[oó]n rechazada\b/iu',
        '/\bconexi[oó]n interrumpida\b/iu',
        '/\bconexi[oó]n perdida\b/iu',
        '/\bservidor no disponible\b/iu',
        '/\bservicio no disponible\b/iu',
        '/\btiempo de espera\b/iu',
        '/\bse agotó el tiempo\b/iu',
        '/\bse agoto el tiempo\b/iu',

        '/\bno internet\b/iu',
        '/\bno network\b/iu',
        '/\bnetwork error\b/iu',
        '/\bnetwork failure\b/iu',
        '/\bconnection failed\b/iu',
        '/\bconnection refused\b/iu',
        '/\bconnection lost\b/iu',
        '/\bconnection interrupted\b/iu',
        '/\bserver unavailable\b/iu',
        '/\bservice unavailable\b/iu',
        '/\bhost unreachable\b/iu',
        '/\bnetwork unreachable\b/iu',
        '/\btimeout\b/iu',
        '/\btimed out\b/iu',
        '/\bdns error\b/iu',
        '/\bdns failure\b/iu',

        /*
        |--------------------------------------------------------------------------
        | Archivos y rutas
        |--------------------------------------------------------------------------
        |
        | Detecta problemas relacionados con archivos faltantes, rutas inválidas, corrupción o fallos al abrir y guardar.
        |
        */

        '/\barchivo no encontrado\b/iu',
        '/\barchivo dañado\b/iu',
        '/\barchivo corrupto\b/iu',
        '/\bruta no encontrada\b/iu',
        '/\bcarpeta no encontrada\b/iu',
        '/\bno se puede abrir el archivo\b/iu',
        '/\bno se pudo guardar\b/iu',
        '/\bno se puede guardar\b/iu',

        '/\bfile not found\b/iu',
        '/\bmissing file\b/iu',
        '/\bfile is missing\b/iu',
        '/\bcorrupt file\b/iu',
        '/\bfile is corrupted\b/iu',
        '/\bpath not found\b/iu',
        '/\bdirectory not found\b/iu',
        '/\bcannot open file\b/iu',
        '/\bunable to open file\b/iu',
        '/\bcannot save\b/iu',
        '/\bfailed to save\b/iu',

        /*
        |--------------------------------------------------------------------------
        | Datos inválidos
        |--------------------------------------------------------------------------
        |
        | Detecta mensajes relacionados con datos incorrectos, formatos no válidos y campos obligatorios faltantes.
        |
        */

        '/\binválid[oa]\b/iu',
        '/\binvalid[oa]\b/iu',
        '/\bdato incorrecto\b/iu',
        '/\bformato incorrecto\b/iu',
        '/\bformato no válido\b/iu',
        '/\bformato no valido\b/iu',
        '/\bcampo requerido\b/iu',
        '/\binformación incompleta\b/iu',
        '/\binformacion incompleta\b/iu',

        '/\binvalid\b/iu',
        '/\bincorrect\b/iu',
        '/\bnot valid\b/iu',
        '/\bmalformed\b/iu',
        '/\bmissing required\b/iu',
        '/\brequired field\b/iu',
        '/\bincomplete data\b/iu',

        /*
        |--------------------------------------------------------------------------
        | Instalación, actualización y ejecución
        |--------------------------------------------------------------------------
        |
        | Detecta errores producidos al instalar, actualizar, iniciar o ejecutar aplicaciones y procesos.
        |
        */

        '/\bno se pudo instalar\b/iu',
        '/\bno se pudo actualizar\b/iu',
        '/\bno se pudo iniciar\b/iu',
        '/\bno se pudo ejecutar\b/iu',
        '/\bno se puede ejecutar\b/iu',
        '/\binstalación fallida\b/iu',
        '/\binstalacion fallida\b/iu',
        '/\bactualización fallida\b/iu',
        '/\bactualizacion fallida\b/iu',

        '/\binstallation failed\b/iu',
        '/\bupdate failed\b/iu',
        '/\bfailed to install\b/iu',
        '/\bfailed to update\b/iu',
        '/\bfailed to start\b/iu',
        '/\bfailed to launch\b/iu',
        '/\bfailed to execute\b/iu',
        '/\bcannot start\b/iu',
        '/\bcannot launch\b/iu',
        '/\bcannot run\b/iu',
        '/\bunable to start\b/iu',
        '/\bunable to launch\b/iu',

        /*
        |--------------------------------------------------------------------------
        | Recursos y almacenamiento
        |--------------------------------------------------------------------------
        |
        | Detecta problemas relacionados con espacio en disco, memoria y disponibilidad de recursos.
        |
        */

        '/\bsin espacio\b/iu',
        '/\bespacio insuficiente\b/iu',
        '/\bmemoria insuficiente\b/iu',
        '/\bsin memoria\b/iu',
        '/\brecurso no disponible\b/iu',

        '/\bnot enough space\b/iu',
        '/\binsufficient disk space\b/iu',
        '/\bdisk full\b/iu',
        '/\bout of memory\b/iu',
        '/\binsufficient memory\b/iu',
        '/\bresource unavailable\b/iu',

        /*
        |--------------------------------------------------------------------------
        | No encontrado o no disponible
        |--------------------------------------------------------------------------
        |
        | Detecta mensajes que indican ausencia, indisponibilidad o falta de respuesta de recursos y páginas.
        |
        */

        '/\bno encontrado\b/iu',
        '/\bno encontrada\b/iu',
        '/\bno se encuentra\b/iu',
        '/\bno existe\b/iu',
        '/\bno disponible\b/iu',
        '/\bno responde\b/iu',
        '/\bpágina no encontrada\b/iu',
        '/\bpagina no encontrada\b/iu',

        '/\bnot found\b/iu',
        '/\bdoes not exist\b/iu',
        '/\bunavailable\b/iu',
        '/\bnot available\b/iu',
        '/\bnot responding\b/iu',
        '/\bpage not found\b/iu',
    ];
}

    /*
    |--------------------------------------------------------------------------
    | Normalizar texto reconocido
    |--------------------------------------------------------------------------
    |
    | Corrige errores frecuentes producidos por OCR, elimina caracteres no
    | útiles y conserva únicamente contenido legible para su análisis.
    |
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
        |
        | Corrige variaciones comunes generadas por Tesseract en códigos y nombres técnicos antes de aplicar los patrones de detección.
        |
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
        |
        | Retira caracteres gráficos que no aportan información al análisis del mensaje reconocido.
        |
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
        |
        | Conserva letras, números, signos de puntuación, separadores y saltos de línea necesarios para interpretar el texto.
        |
        */

        $texto = preg_replace(
            '/[^\p{L}\p{N}\p{P}\p{Z}\n]/u',
            ' ',
            $texto
        );

        return trim($texto);
    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar línea
    |--------------------------------------------------------------------------
    |
    | Elimina caracteres visuales innecesarios y espacios repetidos sin
    | remover códigos, símbolos ni información relevante del mensaje.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Detectar línea irrelevante
    |--------------------------------------------------------------------------
    |
    | Identifica textos propios de botones, archivos o elementos visuales
    | que no aportan información útil para el diagnóstico.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Detectar línea descriptiva
    |--------------------------------------------------------------------------
    |
    | Comprueba si una línea cercana posee longitud y contenido suficientes
    | para utilizarse como contexto descriptivo del error identificado.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Limitar resultado
    |--------------------------------------------------------------------------
    |
    | Reduce la longitud del resultado final para evitar almacenar textos
    | excesivos provenientes del proceso de reconocimiento óptico.
    |
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