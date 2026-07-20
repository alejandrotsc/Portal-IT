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
        return response()->json([
            'ok' => $this->aiService->warmUp(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta JSON tradicional
    |--------------------------------------------------------------------------
    |
    | Se conserva para compatibilidad con solicitudes que no necesiten
    | streaming.
    |
    */

    public function message(
        Request $request
    ): JsonResponse {
        $validated =
            $this->validateChatRequest(
                $request
            );

        $user = $request->user();

        $message = trim(
            (string) (
                $validated['message']
                ?? ''
            )
        );

        $action = isset(
            $validated['action']
        )
            ? trim(
                (string) $validated['action']
            )
            : null;

        $forceAI = (bool) (
            $validated['force_ai']
            ?? false
        );

        $response =
            $this->chatbotService->handle(
                message: $message,
                user: $user,
                action: $action,
                forceAI: $forceAI
            );

        $conversationId =
            $this->saveConversation(
                userId:
                    $user?->getAuthIdentifier(),

                message:
                    $this->buildStoredMessage(
                        $message,
                        $action
                    ),

                response:
                    $response,

                requestedAction:
                    $action
            );

        $response['conversation_id'] =
            $conversationId;

        return response()->json(
            $response
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta progresiva
    |--------------------------------------------------------------------------
    |
    | Acepta:
    |
    | Texto normal:
    | {
    |     "message": "¿Qué es Active Directory?"
    | }
    |
    | Acción interactiva:
    | {
    |     "action": "problema.internet"
    | }
    |
    | Texto enviado directamente a Ollama:
    | {
    |     "message": "La red aparece conectada pero no navega",
    |     "force_ai": true
    | }
    |
    */

    public function stream(
        Request $request
    ): StreamedResponse {
        $validated =
            $this->validateChatRequest(
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

        $action = isset(
            $validated['action']
        )
            ? trim(
                (string) $validated['action']
            )
            : null;

        $forceAI = (bool) (
            $validated['force_ai']
            ?? false
        );

        $storedMessage =
            $this->buildStoredMessage(
                $message,
                $action
            );

        return response()->stream(
            function () use (
                $message,
                $action,
                $forceAI,
                $storedMessage,
                $user,
                $userId
            ): void {
                /*
                 * Evitar compresión y buffering de PHP.
                 */
                if (
                    function_exists('ini_set')
                ) {
                    @ini_set(
                        'zlib.output_compression',
                        '0'
                    );

                    @ini_set(
                        'output_buffering',
                        '0'
                    );
                }

                /*
                 * Cerrar cualquier buffer existente.
                 */
                while (ob_get_level() > 0) {
                    @ob_end_flush();
                }

                /*
                 * Confirmar inmediatamente que Laravel recibió
                 * la solicitud.
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
                 * Forzar la salida en servidores FastCGI que
                 * esperan varios KB antes de transmitir.
                 */
                echo str_repeat(
                    ' ',
                    8192
                )."\n";

                flush();

                try {
                    $response =
                        $this->chatbotService
                            ->handleStream(
                                message: $message,
                                user: $user,

                                onChunk: function (
                                    string $chunk
                                ): void {
                                    $this->emitStreamEvent(
                                        'chunk',
                                        $chunk
                                    );
                                },

                                action: $action,
                                forceAI: $forceAI
                            );

                    $conversationId =
                        $this->saveConversation(
                            userId:
                                $userId,

                            message:
                                $storedMessage,

                            response:
                                $response,

                            requestedAction:
                                $action
                        );

                    $response['conversation_id'] =
                        $conversationId;

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

                            'retryable' =>
                                true,
                        ]
                    );
                }
            },
            200,
            [
                'Content-Type' =>
                    'application/x-ndjson; charset=UTF-8',

                'Cache-Control' =>
                    'no-cache, no-store, must-revalidate',

                'Pragma' =>
                    'no-cache',

                'X-Accel-Buffering' =>
                    'no',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Estado de gestiones
    |--------------------------------------------------------------------------
    |
    | Se mantiene por compatibilidad. El flujo interactivo utiliza
    | la acción gestion.estado sobre /chatbot/stream.
    |
    */

    public function estado(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        if (!$user) {
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
                'conversation_id' => null,
            ]);
        }

        $items =
            $this->gestionStatus->getRecentFor(
                (int) $user->getAuthIdentifier()
            );

        return response()->json([
            'message' =>
                empty($items)
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

            'mode' => 'flow',

            'conversation_id' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar retroalimentación
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

        if (!$user) {
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

        if (!$conversation) {
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
    | Validar mensaje o acción
    |--------------------------------------------------------------------------
    */

    private function validateChatRequest(
        Request $request
    ): array {
        return $request->validate([
            /*
             * Se requiere message cuando no existe action.
             */
            'message' => [
                'nullable',
                'string',
                'max:500',
                'required_without:action',
            ],

            /*
             * Se requiere action cuando no existe message.
             */
            'action' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9_.-]+$/',
                'required_without:message',
            ],

            /*
             * Envía el texto directamente a Ollama.
             */
            'force_ai' => [
                'nullable',
                'boolean',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Texto almacenado en conversación
    |--------------------------------------------------------------------------
    |
    | Cuando el usuario selecciona un botón no existe un mensaje escrito.
    | Guardamos el identificador de la acción para mantener trazabilidad.
    |
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

        if (ob_get_level() > 0) {
            @ob_flush();
        }

        flush();
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
        if (!$userId) {
            return null;
        }

        try {
            $intent =
                $response['intent']
                ?? [];

            $redirect =
                $response['redirect']
                ?? null;

            $savedAction = is_array(
                $redirect
            )
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
             * el registro del historial.
             */
            return null;
        }
    }
}