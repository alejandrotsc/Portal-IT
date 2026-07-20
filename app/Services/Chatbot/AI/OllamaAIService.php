<?php

namespace App\Services\Chatbot\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OllamaAIService implements AIServiceInterface
{
    public function __construct(
        private readonly PromptBuilder $promptBuilder
    ) {}

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
            $message,
            $context,
            static function (string $chunk): void {
                // En modo normal solamente se acumula la respuesta.
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

        if ($message === '') {
            return $this->fallbackResponse();
        }

        $url = rtrim(
            (string) config(
                'chatbot.ai.url',
                'http://127.0.0.1:11434/api/chat'
            ),
            '/'
        );

        $model = (string) config(
            'chatbot.ai.model',
            'llama3.2'
        );

        try {
            $messages = $this->buildMessages(
                $message,
                $context
            );

            /*
             * withOptions(['stream' => true]) hace que Laravel/Guzzle
             * no espere a descargar toda la respuesta.
             *
             * 'stream' => true dentro del JSON indica a Ollama que debe
             * generar una línea JSON por cada fragmento.
             */
            $response = Http::withOptions([
                'stream' => true,
            ])
                ->connectTimeout(
                    (int) config(
                        'chatbot.ai.connect_timeout',
                        3
                    )
                )
                ->timeout(
                    (int) config(
                        'chatbot.ai.timeout',
                        60
                    )
                )
                ->acceptJson()
                ->post(
                    $url,
                    [
                        'model' => $model,

                        'messages' => $messages,

                        'stream' => true,

                        'keep_alive' => config(
                            'chatbot.ai.keep_alive',
                            '30m'
                        ),

                        'options' => [
                            'temperature' => (float) config(
                                'chatbot.ai.temperature',
                                0.1
                            ),

                            'top_p' => (float) config(
                                'chatbot.ai.top_p',
                                0.85
                            ),

                            'num_ctx' => (int) config(
                                'chatbot.ai.num_ctx',
                                1024
                            ),

                            'num_predict' => (int) config(
                                'chatbot.ai.num_predict',
                                120
                            ),

                            'repeat_penalty' => (float) config(
                                'chatbot.ai.repeat_penalty',
                                1.1
                            ),
                        ],
                    ]
                );

            if (!$response->successful()) {
                Log::warning(
                    'Ollama respondió con error',
                    [
                        'url' => $url,
                        'model' => $model,
                        'status' => $response->status(),
                    ]
                );

                return $this->fallbackResponse();
            }

            $body = $response
    ->toPsrResponse()
    ->getBody();

$answer = '';
$metadata = [];

/*
 * Obtener el recurso real de red.
 *
 * Ollama devuelve una línea JSON por cada fragmento.
 * fgets() entrega cada línea apenas aparece.
 */
$resource = $body->detach();

if (!is_resource($resource)) {
    throw new \RuntimeException(
        'No fue posible abrir el stream de Ollama.'
    );
}

try {
    while (
        ($line = fgets($resource)) !== false
    ) {
        $this->processStreamLine(
            $line,
            $answer,
            $metadata,
            $onChunk
        );
    }
} finally {
    /*
     * Cerrar el recurso incluso si ocurre una excepción
     * procesando un fragmento.
     */
    fclose($resource);
}

$answer = trim($answer);

            $answer = trim($answer);

            if ($answer === '') {
                Log::warning(
                    'Ollama devolvió una respuesta vacía',
                    [
                        'url' => $url,
                        'model' => $model,
                    ]
                );

                return $this->fallbackResponse();
            }

            return new AIResponse(
                message: $answer,
                category: 'ti',
                confidence: 0.90,
                metadata: array_merge(
                    [
                        'provider' => 'ollama',
                        'model' => $model,
                    ],
                    $metadata
                )
            );

        } catch (ConnectionException $e) {
            Log::warning(
                'No fue posible conectar con Ollama',
                [
                    'error' => $e->getMessage(),
                    'url' => $url,
                    'model' => $model,
                ]
            );

            return $this->fallbackResponse();

        } catch (Throwable $e) {
            Log::error(
                'Error durante el streaming de Ollama',
                [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $url,
                    'model' => $model,
                ]
            );

            return $this->fallbackResponse();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Construir mensajes para Ollama
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
    | Procesar línea NDJSON
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

        $data = json_decode(
            $line,
            true
        );

        if (!is_array($data)) {
            Log::debug(
                'Fragmento inválido recibido desde Ollama',
                [
                    'line' => mb_substr(
                        $line,
                        0,
                        500
                    ),
                ]
            );

            return;
        }

        if (isset($data['error'])) {
            throw new \RuntimeException(
                (string) $data['error']
            );
        }

        $chunk = (string) data_get(
            $data,
            'message.content',
            ''
        );

        if ($chunk !== '') {
            $answer .= $chunk;

            /*
             * Entrega inmediatamente el fragmento al controlador.
             */
            $onChunk($chunk);
        }

        if (($data['done'] ?? false) === true) {
            $metadata = [
                'total_duration' =>
                    $data['total_duration']
                    ?? null,

                'load_duration' =>
                    $data['load_duration']
                    ?? null,

                'prompt_eval_count' =>
                    $data['prompt_eval_count']
                    ?? null,

                'prompt_eval_duration' =>
                    $data['prompt_eval_duration']
                    ?? null,

                'eval_count' =>
                    $data['eval_count']
                    ?? null,

                'eval_duration' =>
                    $data['eval_duration']
                    ?? null,
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar historial
    |--------------------------------------------------------------------------
    */

    private function prepareHistory(
        mixed $history
    ): array {
        if (!is_array($history)) {
            return [];
        }

        $validatedHistory = [];

        $maxLength = max(
            100,
            (int) config(
                'chatbot.ai.history_message_length',
                250
            )
        );

        foreach ($history as $item) {
            if (
                !is_array($item)
                || !isset(
                    $item['role'],
                    $item['content']
                )
                || !in_array(
                    $item['role'],
                    [
                        'user',
                        'assistant',
                    ],
                    true
                )
            ) {
                continue;
            }

            $content = trim(
                (string) $item['content']
            );

            if ($content === '') {
                continue;
            }

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
| Precargar modelo
|--------------------------------------------------------------------------
|
| Carga llama3.2 en la GPU antes de que el usuario envíe
| su primer mensaje.
|
*/

public function warmUp(): bool
{
    $url = rtrim(
        (string) config(
            'chatbot.ai.url',
            'http://127.0.0.1:11434/api/chat'
        ),
        '/'
    );

    $model = (string) config(
        'chatbot.ai.model',
        'llama3.2'
    );

    try {
        $response = Http::connectTimeout(
            (int) config(
                'chatbot.ai.connect_timeout',
                3
            )
        )
            ->timeout(45)
            ->acceptJson()
            ->post(
                $url,
                [
                    'model' => $model,

                    /*
                     * Una solicitud vacía carga el modelo
                     * sin generar contenido.
                     */
                    'messages' => [],

                    'stream' => false,

                    'keep_alive' => config(
                        'chatbot.ai.keep_alive',
                        '30m'
                    ),
                ]
            );

        if (!$response->successful()) {
            Log::warning(
                'No fue posible precargar Ollama',
                [
                    'status' =>
                        $response->status(),

                    'model' =>
                        $model,
                ]
            );

            return false;
        }

        return true;

    } catch (Throwable $e) {
        Log::warning(
            'Error precargando Ollama',
            [
                'error' =>
                    $e->getMessage(),

                'model' =>
                    $model,
            ]
        );

        return false;
    }
}


    /*
    |--------------------------------------------------------------------------
    | Respuesta de respaldo
    |--------------------------------------------------------------------------
    */

    private function fallbackResponse(): AIResponse
    {
        return new AIResponse(
            message:
                'No pude consultar el asistente técnico en este momento. '
                .'Puedes intentar nuevamente o registrar una incidencia.',

            category: 'system',

            confidence: 0,

            metadata: [
                'provider' => 'fallback',
            ]
        );
    }
}