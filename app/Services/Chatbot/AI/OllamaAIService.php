<?php

declare(strict_types=1);

namespace App\Services\Chatbot\AI;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;
use Throwable;

class OllamaAIService implements AIServiceInterface
{
    public function __construct(
        private readonly PromptBuilder $promptBuilder
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta completa
    |--------------------------------------------------------------------------
    */

    public function ask(
        string $message,
        array $context = []
    ): AIResponse {
        return $this->stream(
            message: $message,
            context: $context,
            onChunk: static function (string $chunk): void {
                /*
                 * En una consulta normal no se envían fragmentos al navegador.
                 * El contenido se acumula internamente y se devuelve completo.
                 */
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta progresiva
    |--------------------------------------------------------------------------
    */

    public function stream(
        string $message,
        array $context,
        callable $onChunk
    ): AIResponse {
        $message = trim($message);
        $context = $this->sanitizeContext($context);

        if ($message === '') {
            return $this->fallbackResponse(
                reason: 'empty_message'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Deduplicación
        |--------------------------------------------------------------------------
        |
        | Evita consultar nuevamente a Ollama cuando el frontend envía dos
        | veces el mismo mensaje en un periodo corto.
        |
        */

        $dedupEnabled = (bool) config(
            'chatbot.request_control.dedup.enabled',
            true
        );

        $dedupKey = $dedupEnabled
            ? $this->dedupKey($message, $context)
            : null;

        if ($dedupKey !== null) {
            $cachedResponse = $this->getCachedResponse(
                $dedupKey
            );

            if ($cachedResponse !== null) {
                Log::info(
                    'Solicitud a Ollama omitida: se reutilizó una respuesta reciente.',
                    [
                        'key' => $dedupKey,
                        'scope' => $this->resolveConversationScope($context),
                        'purpose' => $context['purpose'] ?? 'chat',
                    ]
                );

                /*
                 * En modo streaming se entrega la respuesta completa como un
                 * único fragmento porque ya estaba almacenada en caché.
                 */
                $onChunk($cachedResponse->message);

                return $cachedResponse;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ejecutar consulta exclusiva
        |--------------------------------------------------------------------------
        |
        | Solo puede existir una llamada activa hacia Ollama. Este bloqueo
        | también es utilizado por FormPrefillExtractorService.
        |
        */

        $response = $this->runExclusive(
            callback: fn (): AIResponse => $this->performRequest(
                message: $message,
                context: $context,
                onChunk: $onChunk
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Almacenar respuesta reutilizable
        |--------------------------------------------------------------------------
        |
        | Solamente se almacenan respuestas normales de Ollama. No se guardan
        | respuestas truncadas, ocupadas, vacías ni de respaldo.
        |
        */

        if (
            $dedupKey !== null
            && $response instanceof AIResponse
            && $this->shouldCacheResponse($response)
        ) {
            Cache::put(
                $dedupKey,
                $response->toArray(),
                max(
                    1,
                    (int) config(
                        'chatbot.request_control.dedup.ttl',
                        10
                    )
                )
            );
        }

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | Ejecutar solicitud real hacia Ollama
    |--------------------------------------------------------------------------
    */

    private function performRequest(
        string $message,
        array $context,
        callable $onChunk
    ): AIResponse {
        $url = $this->ollamaUrl();
        $model = $this->ollamaModel();

        try {
            $messages = $this->buildMessages(
                message: $message,
                context: $context
            );

            /*
             * withOptions(['stream' => true]) evita que Guzzle espere a
             * descargar toda la respuesta antes de entregar el cuerpo.
             *
             * El campo stream dentro del JSON indica a Ollama que debe
             * responder utilizando fragmentos NDJSON.
             */
            $response = Http::withOptions([
                'stream' => true,
            ])
                ->asJson()
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
                    $url,
                    [
                        'model' => $model,

                        'messages' => $messages,

                        'stream' => true,

                        'keep_alive' => (string) config(
                            'chatbot.ai.keep_alive',
                            '30m'
                        ),

                        'options' => [
                            'temperature' => $this->normalizedFloatConfig(
                                key: 'chatbot.ai.temperature',
                                default: 0.1,
                                minimum: 0.0,
                                maximum: 2.0
                            ),

                            'top_p' => $this->normalizedFloatConfig(
                                key: 'chatbot.ai.top_p',
                                default: 0.85,
                                minimum: 0.0,
                                maximum: 1.0
                            ),

                            'num_ctx' => max(
                                512,
                                (int) config(
                                    'chatbot.ai.num_ctx',
                                    1024
                                )
                            ),

                            'num_predict' => $this->resolveNumPredict(
                                $context
                            ),

                            'repeat_penalty' => $this->normalizedFloatConfig(
                                key: 'chatbot.ai.repeat_penalty',
                                default: 1.15,
                                minimum: 0.0,
                                maximum: 2.0
                            ),
                        ],
                    ]
                );

            if (! $response->successful()) {
                Log::warning(
                    'Ollama respondió con un estado HTTP no exitoso.',
                    [
                        'url' => $url,
                        'model' => $model,
                        'status' => $response->status(),
                        'response' => mb_substr(
                            $response->body(),
                            0,
                            500
                        ),
                    ]
                );

                return $this->fallbackResponse(
                    reason: 'ollama_http_error',
                    metadata: [
                        'status' => $response->status(),
                    ]
                );
            }

            $body = $response
                ->toPsrResponse()
                ->getBody();

            $resource = $body->detach();

            if (! is_resource($resource)) {
                throw new RuntimeException(
                    'No fue posible abrir el flujo de respuesta de Ollama.'
                );
            }

            $answer = '';
            $metadata = [];

            try {
                while (($line = fgets($resource)) !== false) {
                    $this->processStreamLine(
                        line: $line,
                        answer: $answer,
                        metadata: $metadata,
                        onChunk: $onChunk
                    );
                }
            } finally {
                fclose($resource);
            }

            $answer = trim($answer);

            if ($answer === '') {
                Log::warning(
                    'Ollama devolvió una respuesta vacía.',
                    [
                        'url' => $url,
                        'model' => $model,
                    ]
                );

                return $this->fallbackResponse(
                    reason: 'empty_ollama_response'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Respuesta truncada
            |--------------------------------------------------------------------------
            |
            | done_reason = length indica que Ollama alcanzó num_predict.
            | Se intenta eliminar la parte final incompleta.
            |
            */

            if (($metadata['truncated'] ?? false) === true) {
                $trimmedAnswer = $this->trimIncompleteTail(
                    $answer
                );

                if ($trimmedAnswer !== '') {
                    $answer = $trimmedAnswer;
                }

                Log::info(
                    'Respuesta de Ollama truncada por alcanzar num_predict.',
                    [
                        'model' => $model,
                        'done_reason' => $metadata['done_reason'] ?? null,
                        'eval_count' => $metadata['eval_count'] ?? null,
                    ]
                );
            }

            if (trim($answer) === '') {
                return $this->fallbackResponse(
                    reason: 'empty_response_after_trim'
                );
            }

            return new AIResponse(
                message: $answer,

                category: 'ti',

                confidence: ($metadata['truncated'] ?? false)
                    ? 0.60
                    : 0.90,

                quickActions: [],

                metadata: array_merge(
                    [
                        'provider' => 'ollama',
                        'model' => $model,
                        'purpose' => $context['purpose'] ?? 'chat',
                        'reused' => false,
                    ],
                    $metadata
                )
            );
        } catch (ConnectionException $exception) {
            Log::warning(
                'No fue posible conectar con Ollama.',
                [
                    'exception_class' => $exception::class,
                    'error_code' => (string) $exception->getCode(),
                    'url' => $url,
                    'model' => $model,
                ]
            );

            return $this->fallbackResponse(
                reason: 'ollama_connection_error'
            );
        } catch (Throwable $exception) {
            Log::error(
                'Ocurrió un error durante la consulta a Ollama.',
                [
                    'exception_class' => $exception::class,
                    'error_code' => (string) $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'url' => $url,
                    'model' => $model,
                ]
            );

            return $this->fallbackResponse(
                reason: 'unexpected_ollama_error'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Construir mensajes
    |--------------------------------------------------------------------------
    */

    private function buildMessages(
        string $message,
        array $context
    ): array {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->promptBuilder->systemPrompt(
                    $context
                ),
            ],
        ];

        $history = $this->prepareHistory(
            $context['history'] ?? []
        );

        foreach ($history as $historyMessage) {
            $messages[] = $historyMessage;
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $messages;
    }

    /*
    |--------------------------------------------------------------------------
    | Procesar fragmento NDJSON
    |--------------------------------------------------------------------------
    */

    private function processStreamLine(
        string $line,
        string &$answer,
        array &$metadata,
        callable $onChunk
    ): void {
        $line = trim($line);

        if ($line === '') {
            return;
        }

        try {
            $data = json_decode(
                $line,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            Log::debug(
                'Ollama devolvió un fragmento NDJSON inválido.',
                [
                    'line' => mb_substr(
                        $line,
                        0,
                        500
                    ),
                    'error' => $exception->getMessage(),
                ]
            );

            return;
        }

        if (! is_array($data)) {
            return;
        }

        if (isset($data['error'])) {
            throw new RuntimeException(
                trim((string) $data['error']) !== ''
                    ? (string) $data['error']
                    : 'Ollama devolvió un error sin descripción.'
            );
        }

        $chunk = data_get(
            $data,
            'message.content'
        );

        if (is_string($chunk) && $chunk !== '') {
            $answer .= $chunk;

            /*
             * Entregar inmediatamente el fragmento a la capa superior.
             */
            $onChunk($chunk);
        }

        if (($data['done'] ?? false) !== true) {
            return;
        }

        $doneReason = is_string(
            $data['done_reason'] ?? null
        )
            ? $data['done_reason']
            : null;

        $metadata = [
            'total_duration' => $this->nullableInteger(
                $data['total_duration'] ?? null
            ),

            'load_duration' => $this->nullableInteger(
                $data['load_duration'] ?? null
            ),

            'prompt_eval_count' => $this->nullableInteger(
                $data['prompt_eval_count'] ?? null
            ),

            'prompt_eval_duration' => $this->nullableInteger(
                $data['prompt_eval_duration'] ?? null
            ),

            'eval_count' => $this->nullableInteger(
                $data['eval_count'] ?? null
            ),

            'eval_duration' => $this->nullableInteger(
                $data['eval_duration'] ?? null
            ),

            'done_reason' => $doneReason,

            /*
             * length significa que se alcanzó num_predict.
             */
            'truncated' => $doneReason === 'length',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Recortar final incompleto
    |--------------------------------------------------------------------------
    */

    private function trimIncompleteTail(
        string $text
    ): string {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        /*
         * Si ya termina con puntuación de cierre, se mantiene completo.
         */
        if (preg_match('/[.!?…]["\')\]]?$/u', $text) === 1) {
            return $text;
        }

        /*
         * Buscar la última oración completa.
         */
        if (
            preg_match_all(
                '/[.!?…](?=\s|$)/u',
                $text,
                $matches,
                PREG_OFFSET_CAPTURE
            ) === false
            || empty($matches[0])
        ) {
            /*
             * No existe un punto de corte seguro. Se conserva el contenido
             * para evitar perder completamente una respuesta útil.
             */
            return $text;
        }

        $lastMatch = end($matches[0]);

        if (
            ! is_array($lastMatch)
            || ! isset($lastMatch[0], $lastMatch[1])
        ) {
            return $text;
        }

        $boundary = (int) $lastMatch[1]
            + strlen((string) $lastMatch[0]);

        return trim(
            mb_strcut(
                $text,
                0,
                $boundary,
                'UTF-8'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolver límite de generación
    |--------------------------------------------------------------------------
    */

    private function resolveNumPredict(
        array $context
    ): int {
        $purpose = strtolower(
            trim(
                (string) ($context['purpose'] ?? 'chat')
            )
        );

        if ($purpose === 'prefill') {
            return max(
                64,
                (int) config(
                    'chatbot.ai.num_predict_prefill',
                    320
                )
            );
        }

        return max(
            64,
            (int) config(
                'chatbot.ai.num_predict',
                120
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar historial
    |--------------------------------------------------------------------------
    */

    private function prepareHistory(
        mixed $history
    ): array {
        if (! is_array($history)) {
            return [];
        }

        $validatedHistory = [];

        $maxLength = max(
            100,
            (int) config(
                'chatbot.ai.history_message_length',
                300
            )
        );

        foreach ($history as $item) {
            if (
                ! is_array($item)
                || ! isset($item['role'], $item['content'])
                || ! in_array(
                    $item['role'],
                    ['user', 'assistant'],
                    true
                )
            ) {
                continue;
            }

            $content = trim(
                strip_tags(
                    (string) $item['content']
                )
            );

            if ($content === '') {
                continue;
            }

            $content = preg_replace(
                '/\s+/u',
                ' ',
                $content
            ) ?? $content;

            $validatedHistory[] = [
                'role' => $item['role'],

                'content' => mb_substr(
                    $content,
                    0,
                    $maxLength
                ),
            ];
        }

        $historyLimit = max(
            0,
            (int) config(
                'chatbot.ai.history_limit',
                2
            )
        );

        if ($historyLimit === 0) {
            return [];
        }

        return array_slice(
            $validatedHistory,
            -$historyLimit
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Precargar Ollama
    |--------------------------------------------------------------------------
    */

    public function warmUp(): bool
    {
        $warmupEnabled = (bool) config(
            'chatbot.request_control.warmup.enabled',
            true
        );

        if (! $warmupEnabled) {
            return false;
        }

        $result = $this->runExclusive(
            callback: fn (): bool => $this->performWarmUp(),
            isWarmup: true
        );

        return $result === true;
    }

    private function performWarmUp(): bool
    {
        $url = $this->ollamaUrl();
        $model = $this->ollamaModel();

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
                    $url,
                    [
                        'model' => $model,
                        'stream' => false,
                        'keep_alive' => (string) config(
                            'chatbot.ai.keep_alive',
                            '30m'
                        ),
                    ]
                );

            if (! $response->successful()) {
                Log::warning(
                    'No fue posible precargar Ollama.',
                    [
                        'status' => $response->status(),
                        'model' => $model,
                        'response' => mb_substr(
                            $response->body(),
                            0,
                            500
                        ),
                    ]
                );

                return false;
            }

            Log::info(
                'Modelo de Ollama precargado sin ejecutar inferencia.',
                [
                    'model' => $model,
                ]
            );

            return true;
        } catch (Throwable $exception) {
            Log::warning(
                'Ocurrió un error durante la precarga de Ollama.',
                [
                    'exception_class' => $exception::class,
                    'error_code' => (string) $exception->getCode(),
                    'model' => $model,
                ]
            );

            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Control de solicitud única
    |--------------------------------------------------------------------------
    */

    private function runExclusive(
        callable $callback,
        bool $isWarmup = false
    ): mixed {
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

        /*
         * El warm-up nunca debe esperar. Si hay una consulta activa,
         * simplemente se omite.
         */
        $wait = $isWarmup
            ? 0
            : max(
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
                'No fue posible adquirir el bloqueo de Ollama.',
                [
                    'exception_class' => $exception::class,
                    'error_code' => (string) $exception->getCode(),
                    'lock_key' => $lockKey,
                    'warmup' => $isWarmup,
                ]
            );

            $acquired = false;
        }

        if (! $acquired) {
            if ($isWarmup) {
                Log::info(
                    'Warm-up de Ollama omitido: existe una solicitud activa.',
                    [
                        'lock_key' => $lockKey,
                    ]
                );

                return false;
            }

            Log::info(
                'Solicitud a Ollama omitida: existe otra consulta activa.',
                [
                    'lock_key' => $lockKey,
                ]
            );

            return $this->busyResponse();
        }

        try {
            return $callback();
        } finally {
            try {
                $lock->release();
            } catch (Throwable $exception) {
                Log::warning(
                    'No fue posible liberar el bloqueo de Ollama.',
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
    | Caché y deduplicación
    |--------------------------------------------------------------------------
    */

    private function getCachedResponse(
        string $dedupKey
    ): ?AIResponse {
        $cached = Cache::get($dedupKey);

        if (
            ! is_array($cached)
            || ! isset($cached['message'])
            || ! is_scalar($cached['message'])
            || trim((string) $cached['message']) === ''
        ) {
            return null;
        }

        $metadata = is_array(
            $cached['metadata'] ?? null
        )
            ? $cached['metadata']
            : [];

        return new AIResponse(
            message: trim((string) $cached['message']),

            category: is_scalar($cached['category'] ?? null)
                ? (string) $cached['category']
                : 'ti',

            confidence: is_numeric(
                $cached['confidence'] ?? null
            )
                ? (float) $cached['confidence']
                : 0.90,

            quickActions: is_array(
                $cached['quick_actions'] ?? null
            )
                ? $cached['quick_actions']
                : (
                    is_array($cached['quickActions'] ?? null)
                        ? $cached['quickActions']
                        : []
                ),

            metadata: array_merge(
                $metadata,
                [
                    'provider' => 'ollama',
                    'reused' => true,
                ]
            )
        );
    }

    private function shouldCacheResponse(
        AIResponse $response
    ): bool {
        return $response->hasResponse()
            && $response->isFromOllama()
            && ! $response->isTruncated()
            && ! $response->isBusy()
            && ! $response->isFallback();
    }

    private function dedupKey(
        string $message,
        array $context
    ): string {
        $purpose = strtolower(
            trim(
                (string) ($context['purpose'] ?? 'chat')
            )
        );

        $scope = $this->resolveConversationScope(
            $context
        );

        /*
         * Incorporar el historial y datos relevantes del contexto evita
         * reutilizar una respuesta generada bajo un contexto diferente.
         */
        $contextFingerprint = [
            'history' => $this->prepareHistory(
                $context['history'] ?? []
            ),

            'intent' => $context['intent'] ?? null,

            'flow' => $context['flow'] ?? null,

            'step' => $context['step'] ?? null,

            'rol' => $context['rol'] ?? null,
        ];

        try {
            $encodedContext = json_encode(
                $contextFingerprint,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            $encodedContext = serialize(
                $contextFingerprint
            );
        }

        return 'chatbot_ollama_dedup_'.md5(
            $scope
            .'|'
            .$purpose
            .'|'
            .mb_strtolower($message, 'UTF-8')
            .'|'
            .$encodedContext
        );
    }

    private function resolveConversationScope(
        array $context
    ): string {
        $scope = $context['session_id']
            ?? $context['conversation_id']
            ?? $context['user_id']
            ?? 'global';

        if (! is_scalar($scope)) {
            return 'global';
        }

        $scope = trim(
            (string) $scope
        );

        return $scope !== ''
            ? mb_substr($scope, 0, 200)
            : 'global';
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitizar contexto
    |--------------------------------------------------------------------------
    */

    private function sanitizeContext(
        array $context
    ): array {
        $allowedKeys = [
            'history',
            'purpose',
            'session_id',
            'conversation_id',
            'user_id',
            'usuario',
            'rol',
            'intent',
            'flow',
            'step',
            'category',
            'management_type',
            'tipo_gestion',
        ];

        return array_intersect_key(
            $context,
            array_flip($allowedKeys)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Configuración de Ollama
    |--------------------------------------------------------------------------
    */

    private function ollamaUrl(): string
    {
        $url = trim(
            (string) config(
                'chatbot.ai.url',
                'http://127.0.0.1:11434/api/chat'
            )
        );

        return $url !== ''
            ? rtrim($url, '/')
            : 'http://127.0.0.1:11434/api/chat';
    }

    private function ollamaModel(): string
    {
        $model = trim(
            (string) config(
                'chatbot.ai.model',
                'llama3.2:3b'
            )
        );

        return $model !== ''
            ? $model
            : 'llama3.2:3b';
    }

    private function normalizedFloatConfig(
        string $key,
        float $default,
        float $minimum,
        float $maximum
    ): float {
        $value = config($key, $default);

        if (! is_numeric($value)) {
            return $default;
        }

        return min(
            $maximum,
            max(
                $minimum,
                (float) $value
            )
        );
    }

    private function nullableInteger(
        mixed $value
    ): ?int {
        return is_numeric($value)
            ? (int) $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta de espera
    |--------------------------------------------------------------------------
    */

    private function busyResponse(): AIResponse
    {
        return new AIResponse(
            message:
                'Ya hay una consulta en proceso. Espera unos segundos '
                .'e inténtalo nuevamente.',

            category: 'system',

            confidence: 0.0,

            quickActions: [],

            metadata: [
                'provider' => 'busy',
                'reason' => 'ollama_request_in_progress',
                'reused' => false,
                'truncated' => false,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta de respaldo
    |--------------------------------------------------------------------------
    */

    private function fallbackResponse(
        string $reason = 'ollama_unavailable',
        array $metadata = []
    ): AIResponse {
        return new AIResponse(
            message:
                'No pude consultar el asistente técnico en este momento. '
                .'Puedes intentarlo nuevamente o registrar una incidencia.',

            category: 'system',

            confidence: 0.0,

            quickActions: [
                [
                    'label' => 'Registrar incidencia',
                    'action' => 'redirect',
                    'url' => route('incidencias.create'),
                ],
            ],

            metadata: array_merge(
                [
                    'provider' => 'fallback',
                    'reason' => $reason,
                    'reused' => false,
                    'truncated' => false,
                ],
                $metadata
            )
        );
    }
}