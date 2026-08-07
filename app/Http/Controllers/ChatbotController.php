<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Services\Chatbot\AI\AIServiceInterface;
use App\Services\Chatbot\ChatbotService;
use App\Services\Chatbot\GestionStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/*
|--------------------------------------------------------------------------
| Controlador del chatbot
|--------------------------------------------------------------------------
|
| Coordina las solicitudes del asistente del Portal TI, incluyendo el modo
| tradicional, streaming NDJSON, precarga del proveedor de IA, consulta de
| gestiones, retroalimentación y persistencia del historial.
|
*/

class ChatbotController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dependencias
    |--------------------------------------------------------------------------
    |
    | Recibe los servicios responsables de procesar conversaciones, consultar
    | el estado de gestiones y comunicarse con el proveedor de inteligencia
    | artificial configurado.
    |
    */

    public function __construct(
        private readonly ChatbotService $chatbotService,
        private readonly GestionStatusService $gestionStatus,
        private readonly AIServiceInterface $aiService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Precargar Ollama
    |--------------------------------------------------------------------------
    |
    | Evita precargas duplicadas del modelo, coordina concurrencia mediante bloqueos de caché y conserva una marca temporal mientras el modelo debe permanecer disponible.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Ejecutar precarga del modelo
    |--------------------------------------------------------------------------
    |
    | Comprueba marcas y bloqueos de caché antes de solicitar la activación del
    | proveedor de IA, evitando trabajos pesados repetidos.
    |
    */

    public function warmUp(): JsonResponse
    {
        /*
         * Esta marca dura menos que CHATBOT_AI_KEEP_ALIVE=30m.
         * Evita nuevas precargas mientras el modelo debería
         * continuar residente en Ollama.
         */
        if (Cache::has('chatbot:ollama:warm')) {
            return response()->json([
                'ok' => true,
                'already_warm' => true,
            ]);
        }

        /*
         * Impedir que dos pestañas, usuarios o procesos ejecuten
         * la precarga pesada al mismo tiempo.
         */
        $lock = Cache::lock(
            'chatbot:ollama:warming',
            240
        );

        if (! $lock->get()) {
            return response()->json([
                'ok' => true,
                'warming' => true,
            ]);
        }

        try {
            /*
             * Revisar nuevamente después de obtener el bloqueo,
             * porque otra solicitud pudo completar la precarga
             * justo antes de que este proceso adquiriera el lock.
             */
            if (Cache::has('chatbot:ollama:warm')) {
                return response()->json([
                    'ok' => true,
                    'already_warm' => true,
                ]);
            }

            $ok = $this->aiService->warmUp();

            if ($ok) {
                Cache::put(
                    'chatbot:ollama:warm',
                    true,
                    now()->addMinutes(25)
                );
            }

            return response()->json([
                'ok' => $ok,
                'already_warm' => false,
            ]);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
            ]);

        } finally {
            $lock->release();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta JSON tradicional
    |--------------------------------------------------------------------------
    |
    | Procesa una interacción completa del chatbot mediante una respuesta JSON convencional y registra la conversación cuando existe un usuario autenticado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Procesar mensaje tradicional
    |--------------------------------------------------------------------------
    |
    | Valida la entrada, prepara contexto y delega la interacción al servicio
    | principal antes de registrar el resultado.
    |
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
    |
    | Entrega la respuesta del chatbot como eventos NDJSON, permitiendo mantener activa la conexión y enviar el resultado final de forma progresiva.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Procesar mensaje mediante streaming
    |--------------------------------------------------------------------------
    |
    | Prepara la solicitud, abre una respuesta NDJSON y transmite eventos de
    | inicio, actividad, finalización o error al frontend.
    |
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
    |
    | Consulta las gestiones recientes del usuario autenticado y devuelve acciones rápidas para repetir la consulta o regresar al menú principal.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Consultar gestiones recientes
    |--------------------------------------------------------------------------
    |
    | Devuelve las últimas gestiones asociadas al usuario autenticado junto con
    | las acciones disponibles en el flujo conversacional.
    |
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
    |
    | Registra si una conversación resultó útil, verificando que pertenezca al usuario autenticado.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Registrar retroalimentación
    |--------------------------------------------------------------------------
    |
    | Valida la conversación y almacena la valoración únicamente cuando el
    | registro pertenece al usuario autenticado.
    |
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
    |
    | Desactiva límites y buffers de salida que puedan impedir que los eventos NDJSON lleguen progresivamente al navegador.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Configurar entorno de streaming
    |--------------------------------------------------------------------------
    |
    | Ajusta límites de ejecución y buffering para favorecer la entrega
    | progresiva de datos desde PHP hacia el navegador.
    |
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
    |
    | Valida los datos aceptados por el chatbot y limita longitud, formato y estructura del contexto interactivo.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Validar solicitud del chatbot
    |--------------------------------------------------------------------------
    |
    | Garantiza que exista un mensaje o una acción válida y limita el contexto
    | recibido antes de procesar la conversación.
    |
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

                /*
                 * prefill_source puede conservar hasta 3000 caracteres.
                 * Los demás campos vuelven a limitarse a 1000 dentro de
                 * prepareFlowContext().
                 */
                'max:3000',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar contexto recibido
    |--------------------------------------------------------------------------
    |
    | Filtra las claves permitidas, descarta valores inválidos y limita la longitud antes de enviar el contexto a los servicios internos.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Preparar contexto del flujo
    |--------------------------------------------------------------------------
    |
    | Conserva únicamente las claves reconocidas por el chatbot y aplica
    | límites distintos para campos normales y prefill_source.
    |
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

            /*
             * Texto interno acumulado desde el modo IA. Se utiliza solamente
             * para extraer datos y nunca se envía como campo del formulario.
             */
            'prefill_source',
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
                $key === 'prefill_source'
                    ? 3000
                    : 1000
            );
        }

        return $prepared;
    }

    /*
    |--------------------------------------------------------------------------
    | Texto almacenado en conversación
    |--------------------------------------------------------------------------
    |
    | Genera el texto persistido en el historial a partir del mensaje escrito, la acción ejecutada o una interacción genérica.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Construir mensaje para historial
    |--------------------------------------------------------------------------
    |
    | Determina qué texto debe almacenarse cuando la interacción proviene de un
    | mensaje libre, una acción del flow o un evento genérico.
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
    |
    | Serializa y envía un evento del stream utilizando una estructura uniforme de tipo y datos.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enviar evento del stream
    |--------------------------------------------------------------------------
    |
    | Serializa el evento y fuerza su salida para que el frontend pueda
    | procesarlo inmediatamente.
    |
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
    |
    | Fuerza la entrega inmediata del contenido acumulado para reducir el buffering durante el streaming.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Forzar salida pendiente
    |--------------------------------------------------------------------------
    |
    | Vacía los buffers disponibles y solicita a PHP entregar el contenido
    | acumulado al cliente.
    |
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
    |
    | Persiste la interacción del usuario, respuesta, intención, puntuación y acción sin bloquear el chatbot si el almacenamiento falla.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Persistir conversación
    |--------------------------------------------------------------------------
    |
    | Guarda la interacción y sus metadatos principales cuando existe un
    | usuario autenticado, sin impedir la respuesta si la persistencia falla.
    |
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