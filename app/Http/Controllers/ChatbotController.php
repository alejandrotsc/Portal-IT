<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Services\Chatbot\AI\AIServiceInterface;
use App\Services\Chatbot\ChatbotService;
use App\Services\Chatbot\GestionStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(
        private readonly ChatbotService $chatbotService,
        private readonly GestionStatusService $gestionStatus,
        private readonly AIServiceInterface $aiService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Precargar Ollama
    |--------------------------------------------------------------------------
    */

    public function warmUp(): JsonResponse
    {
        try {
            return response()->json([
                'ok' => $this->aiService->warmUp(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta JSON tradicional
    |--------------------------------------------------------------------------
    */

    public function message(Request $request): JsonResponse
    {
        $validated = $this->validateChatRequest($request);
        $user = $request->user();

        $message = trim(
            (string) (
                $validated['message']
                ?? ''
            )
        );

        $action = isset($validated['action'])
            ? trim((string) $validated['action'])
            : null;

        $forceAI = (bool) (
            $validated['force_ai']
            ?? false
        );

        $flowContext = $this->prepareFlowContext(
            $validated['flow_context']
            ?? []
        );

        $response = $this->chatbotService->handle(
            message: $message,
            user: $user,
            action: $action,
            forceAI: $forceAI,
            flowContext: $flowContext
        );

        $response['conversation_id'] = $this->saveConversation(
            userId: $user?->getAuthIdentifier(),
            message: $this->buildStoredMessage(
                $message,
                $action
            ),
            response: $response,
            requestedAction: $action
        );

        return response()->json(
            $response
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta progresiva NDJSON
    |--------------------------------------------------------------------------
    */

    public function stream(
        Request $request
    ): StreamedResponse {
        $validated = $this->validateChatRequest(
            $request
        );

        $user = $request->user();

        $userId = $user
            ? (int) $user->getAuthIdentifier()
            : null;

        $message = trim(
            (string) (
                $validated['message']
                ?? ''
            )
        );

        $action = isset($validated['action'])
            ? trim((string) $validated['action'])
            : null;

        $forceAI = (bool) (
            $validated['force_ai']
            ?? false
        );

        $flowContext = $this->prepareFlowContext(
            $validated['flow_context']
            ?? []
        );

        $storedMessage = $this->buildStoredMessage(
            $message,
            $action
        );

        return response()->stream(
            function () use (
                $message,
                $action,
                $forceAI,
                $flowContext,
                $storedMessage,
                $user,
                $userId
            ): void {
                $this->prepareStreamingEnvironment();

                /*
                 * Confirmar inmediatamente que Laravel
                 * recibió la solicitud.
                 */
                $this->emitStreamEvent(
                    'start',
                    [
                        'mode' =>
                            $forceAI
                                ? 'ai'
                                : 'flow',
                    ]
                );

                /*
                 * Forzar la salida en FastCGI, proxies
                 * y navegadores que acumulan datos.
                 */
                echo str_repeat(
                    ' ',
                    8192
                )."\n";

                $this->flushOutput();

                try {
                    $response =
                        $this->chatbotService->handleStream(
                            message: $message,
                            user: $user,
                            onChunk: function (
                                string $chunk
                            ): void {
                                if ($chunk === '') {
                                    return;
                                }

                                $this->emitStreamEvent(
                                    'chunk',
                                    $chunk
                                );
                            },
                            action: $action,
                            forceAI: $forceAI,
                            flowContext: $flowContext
                        );

                    $response['conversation_id'] =
                        $this->saveConversation(
                            userId: $userId,
                            message: $storedMessage,
                            response: $response,
                            requestedAction: $action
                        );

                    /*
                     * El frontend exige este evento para
                     * considerar terminada la respuesta.
                     */
                    $this->emitStreamEvent(
                        'complete',
                        $response
                    );
                } catch (Throwable $e) {
                    report($e);

                    $this->emitStreamEvent(
                        'error',
                        [
                            'message' =>
                                'No pude procesar tu solicitud en este momento.',

                            'retryable' => true,
                        ]
                    );
                }
            },
            200,
            [
                'Content-Type' =>
                    'application/x-ndjson; charset=UTF-8',

                'Cache-Control' =>
                    'no-cache, no-store, must-revalidate, no-transform',

                'Pragma' => 'no-cache',

                'Expires' => '0',

                'X-Accel-Buffering' => 'no',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Estado de gestiones
    |--------------------------------------------------------------------------
    */

    public function estado(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' =>
                    'Necesitas iniciar sesión para consultar tus gestiones.',

                'quick_actions' => [
                    [
                        'label' =>
                            'Volver al menú',

                        'action' =>
                            'flow',

                        'value' =>
                            'menu.principal',
                    ],
                ],

                'redirect' => null,

                'items' => [],

                'mode' => 'flow',

                'flow_context' => [],

                'conversation_id' => null,
            ]);
        }

        $items = $this->gestionStatus->getRecentFor(
            (int) $user->getAuthIdentifier()
        );

        return response()->json([
            'message' => empty($items)
                ? 'No encontré gestiones registradas a tu nombre.'
                : 'Estas son tus gestiones recientes:',

            'quick_actions' => [
                [
                    'label' =>
                        'Consultar nuevamente',

                    'action' =>
                        'status',

                    'value' =>
                        'gestion.estado',
                ],

                [
                    'label' =>
                        'Volver al menú',

                    'action' =>
                        'flow',

                    'value' =>
                        'menu.principal',
                ],
            ],

            'redirect' => null,

            'items' =>
                $items ?: [],

            'mode' =>
                'flow',

            'flow_context' =>
                [],

            'conversation_id' =>
                null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Retroalimentación
    |--------------------------------------------------------------------------
    */

    public function feedback(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'conversation_id' => [
                'required',
                'integer',
                'exists:chatbot_conversations,id',
            ],

            'was_helpful' => [
                'required',
                'boolean',
            ],
        ]);

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'ok' => false,

                'message' =>
                    'Necesitas iniciar sesión.',
            ], 401);
        }

        $conversation =
            ChatbotConversation::query()
                ->where(
                    'id',
                    $validated['conversation_id']
                )
                ->where(
                    'usuario_id',
                    $user->getAuthIdentifier()
                )
                ->first();

        if (! $conversation) {
            return response()->json([
                'ok' => false,

                'message' =>
                    'No se encontró la conversación.',
            ], 404);
        }

        $conversation->update([
            'es_util' =>
                $validated['was_helpful'],
        ]);

        return response()->json([
            'ok' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar PHP para streaming
    |--------------------------------------------------------------------------
    */

    private function prepareStreamingEnvironment(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        if (function_exists('ini_set')) {
            @ini_set(
                'max_execution_time',
                '0'
            );

            @ini_set(
                'zlib.output_compression',
                '0'
            );

            @ini_set(
                'output_buffering',
                '0'
            );

            @ini_set(
                'implicit_flush',
                '1'
            );
        }

        if (function_exists('ob_implicit_flush')) {
            @ob_implicit_flush(true);
        }

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validar mensaje, acción y contexto
    |--------------------------------------------------------------------------
    */

    private function validateChatRequest(
        Request $request
    ): array {
        return $request->validate([
            'message' => [
                'nullable',
                'string',
                'max:500',
                'required_without:action',
            ],

            'action' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9_.-]+$/',
                'required_without:message',
            ],

            'force_ai' => [
                'nullable',
                'boolean',
            ],

            /*
             * Contexto acumulado del flujo interactivo.
             */
            'flow_context' => [
                'nullable',
                'array',
                'max:20',
            ],

            'flow_context.*' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar contexto recibido
    |--------------------------------------------------------------------------
    */

    private function prepareFlowContext(
        mixed $context
    ): array {
        if (! is_array($context)) {
            return [];
        }

        $allowedKeys = [
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

        $prepared = [];

        foreach ($context as $key => $value) {
            $key = trim(
                (string) $key
            );

            if (
                ! in_array(
                    $key,
                    $allowedKeys,
                    true
                )
                || ! is_scalar($value)
            ) {
                continue;
            }

            $value = trim(
                (string) $value
            );

            if ($value === '') {
                continue;
            }

            $prepared[$key] = mb_substr(
                $value,
                0,
                1000
            );
        }

        return $prepared;
    }

    /*
    |--------------------------------------------------------------------------
    | Texto almacenado en conversación
    |--------------------------------------------------------------------------
    */

    private function buildStoredMessage(
        string $message,
        ?string $action
    ): string {
        if ($message !== '') {
            return $message;
        }

        if (
            is_string($action)
            && $action !== ''
        ) {
            return "[Acción] {$action}";
        }

        return '[Interacción del chatbot]';
    }

    /*
    |--------------------------------------------------------------------------
    | Emitir evento NDJSON
    |--------------------------------------------------------------------------
    */

    private function emitStreamEvent(
        string $type,
        mixed $data
    ): void {
        $payload = json_encode(
            [
                'type' => $type,
                'data' => $data,
            ],
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($payload === false) {
            return;
        }

        echo $payload."\n";

        $this->flushOutput();
    }

    /*
    |--------------------------------------------------------------------------
    | Vaciar salida
    |--------------------------------------------------------------------------
    */

    private function flushOutput(): void
    {
        if (ob_get_level() > 0) {
            @ob_flush();
        }

        if (function_exists('flush')) {
            @flush();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar conversación
    |--------------------------------------------------------------------------
    */

    private function saveConversation(
        int|string|null $userId,
        string $message,
        array $response,
        ?string $requestedAction = null
    ): ?int {
        if (! $userId) {
            return null;
        }

        try {
            $intent =
                $response['intent']
                ?? [];

            $redirect =
                $response['redirect']
                ?? null;

            $savedAction = is_array($redirect)
                ? (
                    $redirect['url']
                    ?? $requestedAction
                )
                : $requestedAction;

            $conversation =
                ChatbotConversation::create([
                    'usuario_id' =>
                        (int) $userId,

                    'mensaje' =>
                        $message,

                    'respuesta' =>
                        $response['message']
                        ?? null,

                    'intencion_detectada' =>
                        $intent['name']
                        ?? null,

                    'puntuacion' =>
                        $intent['score']
                        ?? null,

                    'accion' =>
                        $savedAction,
                ]);

            return (int) $conversation->id;
        } catch (Throwable $e) {
            report($e);

            /*
             * El chatbot debe responder aunque falle
             * el almacenamiento del historial.
             */
            return null;
        }
    }
}