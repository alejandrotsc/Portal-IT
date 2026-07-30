<?php

declare(strict_types=1);

namespace App\Services\Chatbot\AI;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use Throwable;

class FormPrefillExtractorService
{
    private const TIPO_INCIDENCIA = 'incidencia';

    private const TIPO_SOLICITUD = 'solicitud';

    /**
     * Extrae información del mensaje para prellenar
     * un formulario de incidencia o solicitud.
     */
    public function extract(
        string $message,
        ?string $forcedType = null,
        array $existingContext = []
    ): array {
        $message = trim($message);

        $forcedType = $this->normalizeManagementType(
            $forcedType
        );

        $existingContext = $this->sanitizeExistingContext(
            $existingContext
        );

        if ($message === '') {
            return $this->emptyResult(
                $forcedType,
                'empty_message'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Deduplicación
        |--------------------------------------------------------------------------
        |
        | Si el usuario pulsa dos veces el botón de preparar incidencia o
        | solicitud con el mismo mensaje y contexto, se reutiliza el resultado
        | anterior durante un periodo corto.
        |
        */

        $dedupEnabled = (bool) config(
            'chatbot.request_control.dedup.enabled',
            true
        );

        $dedupKey = $dedupEnabled
            ? $this->dedupKey(
                $message,
                $forcedType,
                $existingContext
            )
            : null;

        if ($dedupKey !== null) {
            $cached = Cache::get($dedupKey);

            if (is_array($cached)) {
                Log::info(
                    'Prellenado omitido: se reutilizó un resultado reciente idéntico.',
                    [
                        'key' => $dedupKey,
                        'tipo_gestion' => $forcedType,
                    ]
                );

                return $cached;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ejecutar una única consulta activa hacia Ollama
        |--------------------------------------------------------------------------
        |
        | El prellenado comparte el mismo bloqueo que el chat. Esto evita que
        | una consulta de chat y otra de prellenado utilicen Ollama al mismo
        | tiempo y saturen los recursos del equipo.
        |
        */

        $result = $this->runExclusive(
            callback: fn (): array => $this->performExtraction(
                $message,
                $forcedType,
                $existingContext
            ),
            forcedType: $forcedType
        );

        /*
        |--------------------------------------------------------------------------
        | Guardar resultado reciente
        |--------------------------------------------------------------------------
        |
        | No se almacena el resultado cuando Ollama está ocupado, ya que se
        | trata de una condición temporal y debe poder intentarse nuevamente.
        |
        */

        if (
            $dedupKey !== null
            && ($result['reason'] ?? null) !== 'ollama_busy'
        ) {
            Cache::put(
                $dedupKey,
                $result,
                max(
                    1,
                    (int) config(
                        'chatbot.request_control.dedup.ttl',
                        10
                    )
                )
            );
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Ejecutar extracción mediante Ollama
    |--------------------------------------------------------------------------
    */

    private function performExtraction(
        string $message,
        ?string $forcedType,
        array $existingContext
    ): array {
        try {
            $response = Http::asJson()
                ->acceptJson()
                ->connectTimeout(
                    max(
                        1,
                        (int) config(
                            'chatbot.ai.connect_timeout',
                            3
                        )
                    )
                )
                ->timeout(
                    max(
                        10,
                        (int) config(
                            'chatbot.ai.timeout',
                            180
                        )
                    )
                )
                ->post(
                    $this->ollamaUrl(),
                    [
                        'model' => (string) config(
                            'chatbot.ai.model',
                            'llama3.2:3b'
                        ),

                        'stream' => false,

                        'keep_alive' => (string) config(
                            'chatbot.ai.keep_alive',
                            '30m'
                        ),

                        'format' => 'json',

                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $this->systemPrompt(
                                    $forcedType
                                ),
                            ],
                            [
                                'role' => 'user',
                                'content' => $message,
                            ],
                        ],

                        'options' => [
                            /*
                             * Temperatura en cero para que la extracción
                             * sea estable y determinista.
                             */
                            'temperature' => 0,

                            'top_p' => (float) config(
                                'chatbot.ai.top_p',
                                0.85
                            ),

                            'num_ctx' => max(
                                512,
                                (int) config(
                                    'chatbot.ai.num_ctx',
                                    1536
                                )
                            ),

                            /*
                             * Límite independiente para el prellenado.
                             */
                            'num_predict' => max(
                                64,
                                (int) config(
                                    'chatbot.ai.num_predict_prefill',
                                    320
                                )
                            ),

                            'repeat_penalty' => (float) config(
                                'chatbot.ai.repeat_penalty',
                                1.15
                            ),
                        ],
                    ]
                )
                ->throw();

            $payload = $response->json();

            if (! is_array($payload)) {
                return $this->emptyResult(
                    $forcedType,
                    'invalid_ollama_payload'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Detectar respuesta truncada
            |--------------------------------------------------------------------------
            |
            | Cuando done_reason es length, Ollama alcanzó num_predict antes
            | de finalizar. En ese caso el JSON podría estar incompleto.
            |
            */

            $truncated = (
                $payload['done_reason'] ?? null
            ) === 'length';

            $content = data_get(
                $payload,
                'message.content'
            );

            if (
                ! is_string($content)
                || trim($content) === ''
            ) {
                return $this->emptyResult(
                    $forcedType,
                    $truncated
                        ? 'truncated_response'
                        : 'empty_model_response'
                );
            }

            $decoded = $this->decodeJson($content);

            if ($decoded === null) {
                Log::warning(
                    'La respuesta de prellenado de Ollama no contiene JSON válido.',
                    [
                        'truncated' => $truncated,
                        'tipo_gestion' => $forcedType,
                    ]
                );

                return $this->emptyResult(
                    $forcedType,
                    $truncated
                        ? 'truncated_response'
                        : 'invalid_json_response'
                );
            }

            return $this->normalizeResult(
                modelResult: $decoded,
                forcedType: $forcedType,
                existingContext: $existingContext,
                truncated: $truncated
            );
        } catch (
            ConnectionException
            | RequestException
            | JsonException $exception
        ) {
            Log::warning(
                'No fue posible extraer datos para prellenado mediante Ollama.',
                [
                    'exception_class' => $exception::class,
                    'error_code' => (string) $exception->getCode(),
                    'tipo_gestion' => $forcedType,
                ]
            );

            return $this->emptyResult(
                $forcedType,
                'ollama_unavailable'
            );
        } catch (Throwable $exception) {
            Log::error(
                'Falló el extractor de prellenado del chatbot.',
                [
                    'exception_class' => $exception::class,
                    'error_code' => (string) $exception->getCode(),
                    'tipo_gestion' => $forcedType,
                ]
            );

            return $this->emptyResult(
                $forcedType,
                'unexpected_error'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Prompt de extracción
    |--------------------------------------------------------------------------
    */

    private function systemPrompt(
        ?string $forcedType
    ): string {
        $typeInstruction = $forcedType !== null
            ? "El tipo de gestión ya fue confirmado como \"{$forcedType}\"."
            : 'Determina si corresponde a una incidencia, una solicitud o ninguna.';

        return <<<PROMPT
Eres un extractor de datos del Portal TI.

{$typeInstruction}

Tu única tarea es devolver JSON válido. No expliques nada y no agregues texto fuera del JSON.

REGLAS:
- Una incidencia representa una falla o problema técnico.
- Una solicitud representa una petición de acceso, instalación, equipo, correo, VPN, impresora u otro servicio.
- No inventes datos que el usuario no proporcionó.
- Si un dato no está presente, usa null.
- Conserva la descripción del usuario con redacción clara y breve.
- No incluyas contraseñas, códigos, tokens ni información sensible.
- tipo_gestion solo puede ser: incidencia, solicitud o null.
- confidence debe ser un número entre 0 y 1.

CAMPOS DE INCIDENCIA:
titulo, descripcion, tiempo_problema, afectacion, equipo, ubicacion.

VALORES CERRADOS DE INCIDENCIA:
- tiempo_problema: hoy, ayer, varios_dias o null.
- afectacion: solo, varios, todos o null.

CAMPOS DE SOLICITUD:
categoria, asunto, descripcion, tipo_equipo, accesorio, programa, sistema,
tipo_acceso, justificacion, usuario_afectado, equipo_actual, motivo_cambio.

CATEGORIAS DE SOLICITUD:
- Computadora o accesorios
- Instalar un programa
- Acceso a un sistema
- VPN
- Impresora
- Cuenta de correo
- Cambio de equipo
- Otra solicitud

FORMATO EXACTO:
{
  "tipo_gestion": "incidencia",
  "confidence": 0.90,
  "campos": {
    "titulo": null,
    "descripcion": null,
    "tiempo_problema": null,
    "afectacion": null,
    "equipo": null,
    "ubicacion": null,
    "categoria": null,
    "asunto": null,
    "tipo_equipo": null,
    "accesorio": null,
    "programa": null,
    "sistema": null,
    "tipo_acceso": null,
    "justificacion": null,
    "usuario_afectado": null,
    "equipo_actual": null,
    "motivo_cambio": null
  }
}
PROMPT;
    }

    /*
    |--------------------------------------------------------------------------
    | Decodificar JSON
    |--------------------------------------------------------------------------
    */

    private function decodeJson(
        string $content
    ): ?array {
        $content = trim($content);

        /*
         * Eliminar bloques Markdown que algunos modelos
         * agregan aunque se solicite únicamente JSON.
         */
        $content = preg_replace(
            '/^```(?:json)?\s*|\s*```$/iu',
            '',
            $content
        ) ?? $content;

        $decoded = json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return is_array($decoded)
            ? $decoded
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar resultado
    |--------------------------------------------------------------------------
    */

    private function normalizeResult(
        array $modelResult,
        ?string $forcedType,
        array $existingContext,
        bool $truncated = false
    ): array {
        $type = $forcedType
            ?? $this->normalizeManagementType(
                $modelResult['tipo_gestion'] ?? null
            );

        if ($type === null) {
            return $this->emptyResult(
                null,
                'management_type_not_detected'
            );
        }

        $rawFields = is_array(
            $modelResult['campos'] ?? null
        )
            ? $modelResult['campos']
            : [];

        $extractedFields = $type === self::TIPO_INCIDENCIA
            ? $this->sanitizeIncidenceFields($rawFields)
            : $this->sanitizeRequestFields($rawFields);

        $existingForType = $type === self::TIPO_INCIDENCIA
            ? $this->sanitizeIncidenceFields($existingContext)
            : $this->sanitizeRequestFields($existingContext);

        /*
        |--------------------------------------------------------------------------
        | Combinar datos
        |--------------------------------------------------------------------------
        |
        | Los valores que el usuario ya ingresó manualmente tienen prioridad
        | sobre los detectados por Ollama. Así no se sobrescribe información
        | previamente confirmada en el formulario.
        |
        */

        $fields = array_replace(
            $extractedFields,
            $existingForType
        );

        $confidence = $this->normalizeConfidence(
            $modelResult['confidence'] ?? 0.70
        );

        return [
            'success' => $fields !== [],
            'tipo_gestion' => $type,
            'campos' => $fields,
            'confidence' => $confidence,
            'source' => 'ollama_form_prefill',
            'truncated' => $truncated,
            'reason' => $fields !== []
                ? null
                : 'no_usable_fields',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitizar campos de incidencia
    |--------------------------------------------------------------------------
    */

    private function sanitizeIncidenceFields(
        array $fields
    ): array {
        $result = [];

        $this->addText(
            $result,
            'titulo',
            $fields['titulo'] ?? null,
            200
        );

        $this->addText(
            $result,
            'descripcion',
            $fields['descripcion'] ?? null,
            2000
        );

        $time = $this->normalizeIncidentTime(
            $fields['tiempo_problema'] ?? null
        );

        if ($time !== null) {
            $result['tiempo_problema'] = $time;
        }

        $impact = $this->normalizeImpact(
            $fields['afectacion'] ?? null
        );

        if ($impact !== null) {
            $result['afectacion'] = $impact;
        }

        $this->addText(
            $result,
            'equipo',
            $fields['equipo'] ?? null,
            150
        );

        $this->addText(
            $result,
            'ubicacion',
            $fields['ubicacion'] ?? null,
            200
        );

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitizar campos de solicitud
    |--------------------------------------------------------------------------
    */

    private function sanitizeRequestFields(
        array $fields
    ): array {
        $result = [];

        $category = $this->normalizeRequestCategory(
            $fields['categoria'] ?? null
        );

        if ($category !== null) {
            $result['categoria'] = $category;
        }

        $limits = [
            'asunto' => 200,
            'descripcion' => 2000,
            'tipo_equipo' => 150,
            'accesorio' => 150,
            'programa' => 200,
            'sistema' => 200,
            'tipo_acceso' => 150,
            'justificacion' => 1000,
            'usuario_afectado' => 200,
            'equipo_actual' => 200,
            'motivo_cambio' => 1000,
        ];

        foreach ($limits as $key => $limit) {
            $this->addText(
                $result,
                $key,
                $fields[$key] ?? null,
                $limit
            );
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitizar contexto existente
    |--------------------------------------------------------------------------
    */

    private function sanitizeExistingContext(
        mixed $context
    ): array {
        if (! is_array($context)) {
            return [];
        }

        $allowed = [
            'titulo',
            'descripcion',
            'tiempo_problema',
            'afectacion',
            'equipo',
            'ubicacion',
            'categoria',
            'asunto',
            'tipo_equipo',
            'accesorio',
            'programa',
            'sistema',
            'tipo_acceso',
            'justificacion',
            'usuario_afectado',
            'equipo_actual',
            'motivo_cambio',
        ];

        return array_intersect_key(
            $context,
            array_flip($allowed)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar tipo de gestión
    |--------------------------------------------------------------------------
    */

    private function normalizeManagementType(
        mixed $type
    ): ?string {
        if (! is_string($type)) {
            return null;
        }

        return match ($this->normalizeText($type)) {
            'incidencia',
            'problema',
            'falla' => self::TIPO_INCIDENCIA,

            'solicitud',
            'peticion',
            'requerimiento' => self::TIPO_SOLICITUD,

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar tiempo del problema
    |--------------------------------------------------------------------------
    */

    private function normalizeIncidentTime(
        mixed $value
    ): ?string {
        if (! is_scalar($value)) {
            return null;
        }

        $value = $this->normalizeText(
            (string) $value
        );

        return match (true) {
            $value === 'hoy',
            str_contains($value, 'esta manana'),
            str_contains($value, 'esta tarde') => 'hoy',

            $value === 'ayer',
            str_contains($value, 'desde ayer') => 'ayer',

            $value === 'varios_dias',
            $value === 'varios dias',
            str_contains($value, 'hace dias'),
            str_contains($value, 'desde hace') => 'varios_dias',

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar afectación
    |--------------------------------------------------------------------------
    */

    private function normalizeImpact(
        mixed $value
    ): ?string {
        if (! is_scalar($value)) {
            return null;
        }

        $value = $this->normalizeText(
            (string) $value
        );

        return match (true) {
            $value === 'solo',
            $value === 'solo yo',
            str_contains($value, 'solo me pasa'),
            str_contains($value, 'solo a mi'),
            str_contains($value, 'un usuario') => 'solo',

            $value === 'varios',
            str_contains($value, 'varias personas'),
            str_contains($value, 'varios usuarios'),
            str_contains($value, 'mi equipo de trabajo') => 'varios',

            $value === 'todos',
            str_contains($value, 'toda la empresa'),
            str_contains($value, 'todo el departamento'),
            str_contains($value, 'a todos') => 'todos',

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar categoría de solicitud
    |--------------------------------------------------------------------------
    */

    private function normalizeRequestCategory(
        mixed $value
    ): ?string {
        if (! is_scalar($value)) {
            return null;
        }

        $value = $this->normalizeText(
            (string) $value
        );

        /*
         * Las categorías más específicas deben evaluarse antes que las
         * categorías generales para evitar clasificaciones incorrectas.
         */

        return match (true) {
            str_contains($value, 'cambio de equipo'),
            str_contains($value, 'reemplazo'),
            str_contains($value, 'renovacion') => 'Cambio de equipo',

            str_contains($value, 'vpn') => 'VPN',

            str_contains($value, 'impresora'),
            str_contains($value, 'impresion') => 'Impresora',

            str_contains($value, 'correo'),
            str_contains($value, 'outlook'),
            str_contains($value, 'email') => 'Cuenta de correo',

            str_contains($value, 'instalar'),
            str_contains($value, 'programa'),
            str_contains($value, 'software') => 'Instalar un programa',

            str_contains($value, 'acceso'),
            str_contains($value, 'permiso'),
            str_contains($value, 'sistema') => 'Acceso a un sistema',

            str_contains($value, 'computadora'),
            str_contains($value, 'accesorio'),
            str_contains($value, 'teclado'),
            str_contains($value, 'mouse'),
            str_contains($value, 'monitor') => 'Computadora o accesorios',

            $value === 'otra solicitud',
            $value === 'otro',
            $value === 'otra' => 'Otra solicitud',

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar confianza
    |--------------------------------------------------------------------------
    */

    private function normalizeConfidence(
        mixed $confidence
    ): float {
        if (! is_numeric($confidence)) {
            return 0.70;
        }

        return round(
            min(
                1,
                max(
                    0,
                    (float) $confidence
                )
            ),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Agregar texto seguro
    |--------------------------------------------------------------------------
    */

    private function addText(
        array &$target,
        string $key,
        mixed $value,
        int $limit
    ): void {
        if (! is_scalar($value)) {
            return;
        }

        $value = trim(
            strip_tags(
                (string) $value
            )
        );

        if ($value === '') {
            return;
        }

        $value = preg_replace(
            '/\s+/u',
            ' ',
            $value
        ) ?? $value;

        $target[$key] = mb_substr(
            $value,
            0,
            $limit
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar texto
    |--------------------------------------------------------------------------
    */

    private function normalizeText(
        string $value
    ): string {
        $value = mb_strtolower(
            trim($value),
            'UTF-8'
        );

        return strtr(
            $value,
            [
                'á' => 'a',
                'é' => 'e',
                'í' => 'i',
                'ó' => 'o',
                'ú' => 'u',
                'ü' => 'u',
                'ñ' => 'n',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | URL de Ollama
    |--------------------------------------------------------------------------
    */

    private function ollamaUrl(): string
    {
        $configuredUrl = trim(
            (string) config(
                'chatbot.ai.url',
                'http://127.0.0.1:11434/api/chat'
            )
        );

        return $configuredUrl !== ''
            ? $configuredUrl
            : 'http://127.0.0.1:11434/api/chat';
    }

    /*
    |--------------------------------------------------------------------------
    | Control de solicitud única activa
    |--------------------------------------------------------------------------
    |
    | Utiliza la misma clave de bloqueo que OllamaAIService. De esta manera,
    | una consulta normal y una extracción para formularios no se ejecutan
    | simultáneamente.
    |
    */

    private function runExclusive(
        callable $callback,
        ?string $forcedType = null
    ): array {
        $lockEnabled = (bool) config(
            'chatbot.request_control.lock.enabled',
            true
        );

        if (! $lockEnabled) {
            return $callback();
        }

        $lockKey = trim(
            (string) config(
                'chatbot.request_control.lock.key',
                'chatbot_ollama_lock'
            )
        );

        if ($lockKey === '') {
            $lockKey = 'chatbot_ollama_lock';
        }

        $ttl = max(
            1,
            (int) config(
                'chatbot.request_control.lock.ttl',
                200
            )
        );

        $wait = max(
            0,
            (int) config(
                'chatbot.request_control.lock.wait',
                0
            )
        );

        $lock = Cache::lock(
            $lockKey,
            $ttl
        );

        try {
            $acquired = $wait > 0
                ? $lock->block($wait)
                : $lock->get();
        } catch (LockTimeoutException) {
            $acquired = false;
        } catch (Throwable $exception) {
            Log::warning(
                'No fue posible adquirir el bloqueo de Ollama para el prellenado.',
                [
                    'exception_class' => $exception::class,
                    'error_code' => (string) $exception->getCode(),
                    'tipo_gestion' => $forcedType,
                ]
            );

            $acquired = false;
        }

        if (! $acquired) {
            Log::info(
                'Prellenado omitido: ya existe una solicitud activa hacia Ollama.',
                [
                    'tipo_gestion' => $forcedType,
                    'lock_key' => $lockKey,
                ]
            );

            return $this->emptyResult(
                $forcedType,
                'ollama_busy'
            );
        }

        try {
            return $callback();
        } finally {
            try {
                $lock->release();
            } catch (Throwable $exception) {
                Log::warning(
                    'No fue posible liberar el bloqueo de Ollama después del prellenado.',
                    [
                        'exception_class' => $exception::class,
                        'error_code' => (string) $exception->getCode(),
                        'lock_key' => $lockKey,
                    ]
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Clave de deduplicación
    |--------------------------------------------------------------------------
    */

    private function dedupKey(
        string $message,
        ?string $forcedType,
        array $existingContext
    ): string {
        ksort($existingContext);

        try {
            $contextJson = json_encode(
                $existingContext,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            $contextJson = serialize(
                $existingContext
            );
        }

        return 'chatbot_prefill_dedup_'.md5(
            ($forcedType ?? 'auto')
            .'|'
            .mb_strtolower(
                trim($message),
                'UTF-8'
            )
            .'|'
            .$contextJson
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resultado vacío
    |--------------------------------------------------------------------------
    */

    private function emptyResult(
        ?string $type,
        string $reason
    ): array {
        return [
            'success' => false,
            'tipo_gestion' => $type,
            'campos' => [],
            'confidence' => 0.0,
            'source' => 'ollama_form_prefill',
            'truncated' => false,
            'reason' => $reason,
        ];
    }
}