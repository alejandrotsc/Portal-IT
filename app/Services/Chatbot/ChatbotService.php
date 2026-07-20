<?php

namespace App\Services\Chatbot;

use App\Services\Chatbot\AI\AIResponse;
use App\Services\Chatbot\AI\AIServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class ChatbotService
{
    public function __construct(
        private readonly IntentRecognizerInterface $recognizer,
        private readonly ChatbotResponseBuilder $responseBuilder,
        private readonly GestionStatusService $gestionStatus,
        private readonly AIServiceInterface $aiService,
        private readonly ConversationContextService $contextService,
        private readonly ChatbotFlowService $flowService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Respuesta tradicional
    |--------------------------------------------------------------------------
    */

    public function handle(
        string $message,
        ?Authenticatable $user = null,
        ?string $action = null,
        bool $forceAI = false
    ): array {
        return $this->handleStream(
            message: $message,
            user: $user,

            onChunk: static function (
                string $chunk
            ): void {
                // Compatibilidad sin streaming.
            },

            action: $action,
            forceAI: $forceAI
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Procesar mensaje o acción
    |--------------------------------------------------------------------------
    */

    public function handleStream(
        string $message,
        ?Authenticatable $user,
        callable $onChunk,
        ?string $action = null,
        bool $forceAI = false
    ): array {
        $message = trim($message);

        $action = is_string($action)
            ? trim($action)
            : null;

        $userName =
            $user?->nombre
            ?? config(
                'chatbot.fallback_name',
                'usuario'
            );

        $userId = $user
            ? (int) $user->getAuthIdentifier()
            : null;

        /*
        |--------------------------------------------------------------------------
        | Acción interactiva
        |--------------------------------------------------------------------------
        |
        | Las acciones de botones no pasan por keywords ni por Ollama.
        |
        */

        if ($action !== null && $action !== '') {
            return $this->handleAction(
                $action,
                $userId,
                $userName
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Mensaje vacío: mostrar menú
        |--------------------------------------------------------------------------
        */

        if ($message === '') {
            return $this->flowService->menu(
                $userName
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Reconocer intención
        |--------------------------------------------------------------------------
        */

        $intent = $this->recognizer->recognize(
            $message
        );

        /*
        |--------------------------------------------------------------------------
        | Texto enviado explícitamente a la IA
        |--------------------------------------------------------------------------
        */

        if ($forceAI) {
            return $this->askAI(
                message: $message,
                intent: $intent,
                user: $user,
                userId: $userId,
                userName: $userName,
                onChunk: $onChunk,
                useLocalFallback: false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Consultar gestiones
        |--------------------------------------------------------------------------
        */

        if ($intent->is('consultar_estado')) {
            return $this->buildEstadoResponse(
                $userId,
                $userName
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Menú interactivo
        |--------------------------------------------------------------------------
        */

        if (
            $intent->is('menu')
            || $intent->is('saludo')
        ) {
            return $this->flowService->menu(
                $userName
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Problema técnico escrito directamente
        |--------------------------------------------------------------------------
        |
        | Si identificamos que existe un problema, guiamos al usuario mediante
        | botones. Solo una solicitud explícita para registrar incidencia abre
        | la respuesta local tradicional.
        |
        */

        if ($intent->is('incidencia')) {
            if (
                $this->isDirectIncidenceCommand(
                    $message
                )
            ) {
                return $this->responseBuilder->build(
                    $intent,
                    $userName,
                    $message
                );
            }

            $suggestedFlow =
                $this->detectProblemFlow(
                    $message
                );

            if ($suggestedFlow !== null) {
                return $this->flowService->handle(
                    $suggestedFlow,
                    $userName
                ) ?? $this->flowService->handle(
                    'problema.menu',
                    $userName
                );
            }

            return $this->flowService->handle(
                'problema.menu',
                $userName
            ) ?? $this->flowService->menu(
                $userName
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Acciones conocidas del portal
        |--------------------------------------------------------------------------
        */

        if (
            $intent->is('solicitud')
            || $intent->is('pase_menor_24h')
            || $intent->is('autorizacion_memorando')
            || $intent->is('cierre')
        ) {
            return $this->responseBuilder->build(
                $intent,
                $userName,
                $message
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Texto libre no reconocido: utilizar Ollama
        |--------------------------------------------------------------------------
        */

        return $this->askAI(
            message: $message,
            intent: $intent,
            user: $user,
            userId: $userId,
            userName: $userName,
            onChunk: $onChunk,
            useLocalFallback: true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Procesar acción interactiva
    |--------------------------------------------------------------------------
    */

    private function handleAction(
        string $action,
        ?int $userId,
        string $userName
    ): array {
        /*
         * Acción especial que consulta la base de datos.
         */
        if ($action === 'gestion.estado') {
            return $this->buildEstadoResponse(
                $userId,
                $userName
            );
        }

        $response = $this->flowService->handle(
            $action,
            $userName
        );

        if ($response !== null) {
            return $response;
        }

        /*
         * No procesar acciones desconocidas.
         */
        return [
            'message' =>
                'No pude identificar esa opción. Selecciona nuevamente lo que necesitas.',

            'quick_actions' => [
                [
                    'label' => 'Mostrar menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],
            ],

            'redirect' => null,

            'items' => [],

            'mode' => 'flow',

            'intent' => [
                'name' => 'unknown_action',
                'score' => 0,
                'confidence' => 0,
                'action' => $action,
            ],

            'ai' => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Consultar Ollama
    |--------------------------------------------------------------------------
    */

    private function askAI(
        string $message,
        IntentResult $intent,
        ?Authenticatable $user,
        ?int $userId,
        string $userName,
        callable $onChunk,
        bool $useLocalFallback
    ): array {
        $context = $this->buildAIContext(
            $intent,
            $user,
            $userId,
            $userName
        );

        $aiResponse = $this->aiService->stream(
            $message,
            $context,
            $onChunk
        );

        /*
         * Si Ollama falla y existe una intención útil,
         * podemos utilizar la respuesta local.
         */
        if (
            $useLocalFallback
            && $aiResponse->confidence < 0.60
            && !$intent->is('desconocido')
        ) {
            return $this->responseBuilder->build(
                $intent,
                $userName,
                $message
            );
        }

        return $this->buildAIResponse(
            $intent,
            $userName,
            $message,
            $aiResponse
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Detectar flujo técnico
    |--------------------------------------------------------------------------
    |
    | Esta detección solo selecciona una categoría. El diagnóstico posterior
    | utiliza identificadores exactos enviados por botones.
    |
    */

    private function detectProblemFlow(
        string $message
    ): ?string {
        $message = $this->normalizeText(
            $message
        );

        $flows = [
            'problema.internet' => [
                'internet',
                'wifi',
                'wi fi',
                'red',
                'sin conexion',
                'no conecta',
            ],

            'problema.correo' => [
                'correo',
                'outlook',
                'email',
                'correos',
                'bandeja de entrada',
            ],

            'problema.lentitud' => [
                'lento',
                'lenta',
                'lentitud',
                'se congela',
                'se traba',
                'se queda pegado',
            ],

            'problema.encendido' => [
                'no enciende',
                'no prende',
                'no arranca',
                'no inicia',
                'pantalla negra',
            ],

            'problema.impresora' => [
                'impresora',
                'imprimir',
                'no imprime',
                'papel atascado',
            ],

            'problema.sistema' => [
                'sistema',
                'aplicacion',
                'programa',
                'software',
                'no abre',
                'no responde',
            ],

            'problema.periferico' => [
                'teclado',
                'mouse',
                'raton',
                'monitor',
                'pantalla',
                'audifonos',
                'usb',
            ],
        ];

        foreach (
            $flows as $flow => $keywords
        ) {
            foreach ($keywords as $keyword) {
                if (
                    str_contains(
                        $message,
                        $keyword
                    )
                ) {
                    return $flow;
                }
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Comando directo para incidencia
    |--------------------------------------------------------------------------
    */

    private function isDirectIncidenceCommand(
        string $message
    ): bool {
        $message = $this->normalizeText(
            $message
        );

        $commands = [
            'quiero reportar una incidencia',
            'quiero crear una incidencia',
            'reportar incidencia',
            'crear incidencia',
            'nueva incidencia',
            'abrir incidencia',
            'registrar incidencia',
            'reporte de incidencia',
            'necesito reportar un problema',
            'quiero reportar un problema',
        ];

        foreach ($commands as $command) {
            if (
                str_contains(
                    $message,
                    $command
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Construir contexto para Ollama
    |--------------------------------------------------------------------------
    */

    private function buildAIContext(
        IntentResult $intent,
        ?Authenticatable $user,
        ?int $userId,
        string $userName
    ): array {
        $history = $this->contextService->getRecent(
            $userId,
            (int) config(
                'chatbot.ai.history_limit',
                2
            )
        );

        return [
            'intent' =>
                $intent->intent,

            'usuario' =>
                $userName,

            'rol' =>
                $user?->rol?->nombre
                ?? null,

            'history' =>
                $history,

            'sistemas' => [
                'Windows',
                'Microsoft 365',
                'Outlook',
                'Equipos Dell',
                'Impresoras',
                'VPN',
                'Redes',
                'Active Directory',
                'Aplicaciones internas',
                'Portal TI',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Construir respuesta final de IA
    |--------------------------------------------------------------------------
    */

    private function buildAIResponse(
        IntentResult $originalIntent,
        string $userName,
        string $message,
        AIResponse $aiResponse
    ): array {
        $response =
            $this->responseBuilder->build(
                new IntentResult(
                    intent: 'ai',

                    score:
                        $originalIntent->score,

                    matchedKeywords:
                        $originalIntent->matchedKeywords,

                    confidence:
                        $aiResponse->confidence
                ),

                $userName,

                $message,

                $aiResponse
            );

        $response['mode'] = 'ai';

        $quickActions = is_array(
            $response['quick_actions']
            ?? null
        )
            ? $response['quick_actions']
            : [];

        /*
         * Permitir regresar siempre al asistente interactivo.
         */
        $quickActions[] = [
            'label' => 'Volver al menú',
            'action' => 'flow',
            'value' => 'menu.principal',
        ];

        $response['quick_actions'] =
            $quickActions;

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | Estado de gestiones
    |--------------------------------------------------------------------------
    */

    private function buildEstadoResponse(
        ?int $userId,
        string $userName
    ): array {
        if (!$userId) {
            return [
                'message' =>
                    'Necesitas iniciar sesión para consultar tus gestiones.',

                'quick_actions' => [
                    [
                        'label' => 'Volver al menú',
                        'action' => 'flow',
                        'value' => 'menu.principal',
                    ],
                ],

                'redirect' => null,
                'items' => [],
                'mode' => 'flow',

                'intent' => [
                    'name' => 'consultar_estado',
                    'score' => 1,
                    'confidence' => 1,
                ],

                'ai' => null,
            ];
        }

        $summary = $this->gestionStatus->getSummaryFor(
            $userId,
            5
        );

        $items = is_array(
            $summary['items']
            ?? null
        )
            ? $summary['items']
            : [];

        $total = (int) (
            $summary['total']
            ?? 0
        );

        $abiertas = (int) (
            $summary['abiertas']
            ?? 0
        );

        $enProceso = (int) (
            $summary['en_proceso']
            ?? 0
        );

        $finalizadas = (int) (
            $summary['finalizadas']
            ?? 0
        );

        if ($total === 0) {
            return [
                'message' =>
                    "No encontré gestiones registradas a tu nombre, {$userName}.",

                'quick_actions' => [
                    [
                        'label' => 'Tengo un problema',
                        'action' => 'flow',
                        'value' => 'problema.menu',
                    ],

                    [
                        'label' => 'Crear solicitud',
                        'action' => 'flow',
                        'value' => 'solicitud.menu',
                    ],

                    [
                        'label' => 'Volver al menú',
                        'action' => 'flow',
                        'value' => 'menu.principal',
                    ],
                ],

                'redirect' => null,
                'items' => [],
                'mode' => 'flow',

                'intent' => [
                    'name' => 'consultar_estado',
                    'score' => 1,
                    'confidence' => 1,
                ],

                'ai' => null,
            ];
        }

        $responseMessage =
            "Encontré {$total} "
            .$this->pluralize(
                $total,
                'gestión registrada',
                'gestiones registradas'
            )
            .".\n\n";

        if ($abiertas > 0) {
            $responseMessage .=
                "• Abiertas o pendientes: {$abiertas}\n";
        }

        if ($enProceso > 0) {
            $responseMessage .=
                "• En proceso: {$enProceso}\n";
        }

        if ($finalizadas > 0) {
            $responseMessage .=
                "• Finalizadas: {$finalizadas}\n";
        }

        if ($items !== []) {
            $responseMessage .=
                "\nTe muestro las gestiones más recientes.";
        }

        return [
            'message' =>
                trim($responseMessage),

            'quick_actions' => [
                [
                    'label' => 'Actualizar',
                    'action' => 'status',
                    'value' => 'gestion.estado',
                ],

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],
            ],

            'redirect' => null,
            'items' => $items,
            'mode' => 'flow',

            'intent' => [
                'name' => 'consultar_estado',
                'score' => 1,
                'confidence' => 1,
            ],

            'ai' => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar texto
    |--------------------------------------------------------------------------
    */

    private function normalizeText(
        string $text
    ): string {
        $text = mb_strtolower(
            trim($text),
            'UTF-8'
        );

        return strtr(
            $text,
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
    | Singular y plural
    |--------------------------------------------------------------------------
    */

    private function pluralize(
        int $quantity,
        string $singular,
        string $plural
    ): string {
        return $quantity === 1
            ? $singular
            : $plural;
    }
}