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
        bool $forceAI = false,
        array $flowContext = []
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
            forceAI: $forceAI,
            flowContext: $flowContext
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
        bool $forceAI = false,
        array $flowContext = []
    ): array {
        $message = trim($message);

        $flowContext = $this->prepareFlowContext(
            $flowContext
        );

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
                action: $action,
                userId: $userId,
                userName: $userName,
                flowContext: $flowContext
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
            /*
             * Los diagnósticos conocidos tienen prioridad sobre Ollama.
             *
             * Esto evita que un modelo pequeño genere instrucciones
             * técnicas, repetitivas o inventadas para problemas comunes.
             * Las consultas que no coincidan continúan normalmente hacia IA.
             */
            $diagnostic = $this->detectLocalDiagnostic(
                $message
            );

            if ($diagnostic !== null) {
                return $this->buildLocalDiagnosticResponse(
                    diagnostic: $diagnostic,
                    userName: $userName,
                    flowContext: $flowContext
                );
            }

            return $this->askAI(
                message: $message,
                intent: $intent,
                user: $user,
                userId: $userId,
                userName: $userName,
                onChunk: $onChunk,
                useLocalFallback: false,
                flowContext: $flowContext
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
                    $userName,
                    $flowContext
                ) ?? $this->flowService->handle(
                    'problema.menu',
                    $userName,
                    $flowContext
                );
            }

            return $this->flowService->handle(
                'problema.menu',
                $userName,
                $flowContext
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
            $fallback = $this->responseBuilder->build(
                $intent,
                $userName,
                $message
            );

            $fallback['flow_context'] = $flowContext;

            return $fallback;
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
            useLocalFallback: true,
            flowContext: $flowContext
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
        string $userName,
        array $flowContext = []
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

        /*
         * Regresar al menú comienza un recorrido nuevo.
         */
        if ($action === 'menu.principal') {
            $flowContext = [];
        }

        $response = $this->flowService->handle(
            $action,
            $userName,
            $flowContext
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

            'flow_context' => $flowContext,

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
        bool $useLocalFallback,
        array $flowContext = []
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

        $response = $this->buildAIResponse(
            $intent,
            $userName,
            $message,
            $aiResponse,
            $flowContext
        );

        /*
         * Un modelo pequeño puede ignorar parcialmente el prompt.
         * Antes de mostrar la respuesta, validamos que no incluya
         * instrucciones avanzadas ni más de cuatro pasos.
         */
        if (
            ! $this->isSafeGeneratedMessage(
                $response['message']
                ?? ''
            )
        ) {
            return $this->buildSafeAIFallback(
                userName: $userName,
                flowContext: $flowContext
            );
        }

        return $response;
    }


    /*
    |--------------------------------------------------------------------------
    | Detectar diagnóstico local
    |--------------------------------------------------------------------------
    */

    private function detectLocalDiagnostic(
        string $message
    ): ?array {
        if (! $this->looksLikeDiagnosticRequest($message)) {
            return null;
        }

        $diagnostics = config(
            'chatbot_diagnostics.diagnosticos',
            []
        );

        if (! is_array($diagnostics)) {
            return null;
        }

        $normalizedMessage = $this->normalizeText(
            $message
        );

        $bestDiagnostic = null;
        $bestScore = 0;

        foreach (
            $diagnostics as $key => $diagnostic
        ) {
            if (! is_array($diagnostic)) {
                continue;
            }

            $keywords = $diagnostic['keywords']
                ?? [];

            if (! is_array($keywords)) {
                continue;
            }

            $score = 0;
            $matched = [];

            foreach (
                $keywords as $keyword => $weight
            ) {
                $normalizedKeyword =
                    $this->normalizeText(
                        (string) $keyword
                    );

                if (
                    $normalizedKeyword !== ''
                    && str_contains(
                        $normalizedMessage,
                        $normalizedKeyword
                    )
                ) {
                    $score += max(
                        1,
                        (int) $weight
                    );

                    $matched[] =
                        (string) $keyword;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;

                $bestDiagnostic = [
                    'key' => (string) $key,
                    'message' => trim(
                        (string) (
                            $diagnostic['message']
                            ?? ''
                        )
                    ),
                    'steps' => array_values(
                        array_filter(
                            is_array(
                                $diagnostic['steps']
                                ?? null
                            )
                                ? $diagnostic['steps']
                                : [],
                            static fn (mixed $step): bool =>
                                is_scalar($step)
                                && trim(
                                    (string) $step
                                ) !== ''
                        )
                    ),
                    'matched_keywords' =>
                        $matched,
                    'score' => $score,
                ];
            }
        }

        $minimumScore = max(
            1,
            (int) config(
                'chatbot_diagnostics.minimum_score',
                1
            )
        );

        if (
            $bestDiagnostic === null
            || $bestScore < $minimumScore
        ) {
            return null;
        }

        return $bestDiagnostic;
    }


    /*
    |--------------------------------------------------------------------------
    | Confirmar que el usuario describe una falla
    |--------------------------------------------------------------------------
    */

    private function looksLikeDiagnosticRequest(
        string $message
    ): bool {
        $message = $this->normalizeText(
            $message
        );

        $signals = [
            'diagnostico',
            'diagnosticar',
            'falla',
            'fallo',
            'problema',
            'no funciona',
            'no abre',
            'no conecta',
            'no enciende',
            'no prende',
            'no imprime',
            'sin internet',
            'sin conexion',
            'esta lento',
            'esta lenta',
            'se cae',
            'se desconecta',
            'se congela',
            'se traba',
            'virus',
            'infectado',
        ];

        foreach ($signals as $signal) {
            if (str_contains($message, $signal)) {
                return true;
            }
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Construir diagnóstico controlado
    |--------------------------------------------------------------------------
    */

    private function buildLocalDiagnosticResponse(
        array $diagnostic,
        string $userName,
        array $flowContext = []
    ): array {
        $key = trim(
            (string) (
                $diagnostic['key']
                ?? ''
            )
        );

        $message = trim(
            (string) (
                $diagnostic['message']
                ?? 'Parece que hay un problema técnico.'
            )
        );

        $steps = array_slice(
            is_array(
                $diagnostic['steps']
                ?? null
            )
                ? $diagnostic['steps']
                : [],
            0,
            4
        );

        if ($steps !== []) {
            $message .= "\n\n";

            foreach (
                $steps as $index => $step
            ) {
                $message .= (
                    $index + 1
                )
                    .'. '
                    .rtrim(
                        trim(
                            (string) $step
                        ),
                        '.'
                    )
                    ."\.\n";
            }

            $message = rtrim($message);
        }

        $message .=
            "\n\nSi el problema continúa, registra una incidencia para que el equipo de TI pueda revisarlo.";

        $flowContext = array_merge(
            $flowContext,
            $this->diagnosticPrefill(
                $key
            )
        );

        $aiFlowResponse = $this->flowService->handle(
            'ai.enable',
            $userName,
            $flowContext
        );

        return [
            'message' => $message,

            'quick_actions' => is_array(
                $aiFlowResponse['quick_actions']
                ?? null
            )
                ? $aiFlowResponse['quick_actions']
                : [],

            'redirect' => null,

            'items' => [],

            /*
             * Se conserva el modo IA para permitir que el usuario
             * escriba una aclaración sin regresar al menú.
             */
            'mode' => 'ai',

            'flow_context' => $flowContext,

            'intent' => [
                'name' => 'diagnostico_local',
                'diagnostic' => $key,
                'score' => (int) (
                    $diagnostic['score']
                    ?? 0
                ),
                'confidence' => 1,
                'matched_keywords' =>
                    $diagnostic['matched_keywords']
                    ?? [],
            ],

            'ai' => [
                'source' => 'local_diagnostic',
                'confidence' => 1,
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Datos sugeridos para una incidencia
    |--------------------------------------------------------------------------
    */

    private function diagnosticPrefill(
        string $diagnostic
    ): array {
        return match ($diagnostic) {
            'internet' => [
                'titulo' =>
                    'Problema con internet o WiFi',
                'equipo' =>
                    'Red / WiFi',
            ],

            'correo' => [
                'titulo' =>
                    'Problema con Outlook o correo',
                'equipo' =>
                    'Outlook / Correo corporativo',
            ],

            'equipo_lento' => [
                'titulo' =>
                    'Equipo lento o congelado',
                'equipo' =>
                    'Computadora',
            ],

            'pc_no_enciende' => [
                'titulo' =>
                    'Equipo no enciende',
                'equipo' =>
                    'Computadora',
            ],

            'impresora' => [
                'titulo' =>
                    'Problema con impresora',
                'equipo' =>
                    'Impresora',
            ],

            'sistema' => [
                'titulo' =>
                    'Problema con sistema o aplicación',
                'equipo' =>
                    'Sistema / Aplicación',
            ],

            'perifericos' => [
                'titulo' =>
                    'Problema con periférico',
                'equipo' =>
                    'Periférico',
            ],

            'virus' => [
                'titulo' =>
                    'Comportamiento sospechoso en el equipo',
                'equipo' =>
                    'Computadora',
            ],

            default => [],
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Validar respuesta generada
    |--------------------------------------------------------------------------
    */

    private function isSafeGeneratedMessage(
        mixed $message
    ): bool {
        if (! is_string($message)) {
            return false;
        }

        $message = trim($message);

        if ($message === '') {
            return false;
        }

        $normalized = $this->normalizeText(
            $message
        );

        $forbiddenPatterns = [
            'cmd',
            'powershell',
            'regedit',
            'terminal',
            'simbolo del sistema',
            'ejecuta el comando',
            'ejecutar el comando',
            'direccion ip',
            'cambiar dns',
            'configuracion del router',
            'configuracion del modem',
            'configuracion avanzada de red',
            'reinicia el router',
            'reiniciar el router',
            'reinicia el modem',
            'reiniciar el modem',
            'desactiva el antivirus',
            'desactivar el antivirus',
            'desactiva el firewall',
            'desactivar el firewall',
            'registro de windows',
            'servicios de windows',
            'como administrador',
        ];

        foreach (
            $forbiddenPatterns as $pattern
        ) {
            if (
                str_contains(
                    $normalized,
                    $pattern
                )
            ) {
                return false;
            }
        }

        preg_match_all(
            '/(?:^|\R)\s*\d+[\.)]\s+/u',
            $message,
            $matches
        );

        if (
            count(
                $matches[0]
                ?? []
            ) > 4
        ) {
            return false;
        }

        /*
         * Los modelos pequeños a veces repiten una segunda lista
         * completa después de haber dado ya cuatro pasos.
         */
        if (
            preg_match_all(
                '/\*\*paso\s+\d+/iu',
                $message
            ) > 4
        ) {
            return false;
        }

        return mb_strlen($message) <= 1800;
    }


    /*
    |--------------------------------------------------------------------------
    | Respuesta segura cuando Ollama no cumple las reglas
    |--------------------------------------------------------------------------
    */

    private function buildSafeAIFallback(
        string $userName,
        array $flowContext = []
    ): array {
        $aiFlowResponse = $this->flowService->handle(
            'ai.enable',
            $userName,
            $flowContext
        );

        return [
            'message' =>
                'No pude generar una recomendación suficientemente clara y segura. Describe brevemente qué está fallando o registra una incidencia para que el equipo de TI pueda revisarlo.',

            'quick_actions' => is_array(
                $aiFlowResponse['quick_actions']
                ?? null
            )
                ? $aiFlowResponse['quick_actions']
                : [],

            'redirect' => null,

            'items' => [],

            'mode' => 'ai',

            'flow_context' => $flowContext,

            'intent' => [
                'name' => 'ai_safe_fallback',
                'score' => 0,
                'confidence' => 0,
            ],

            'ai' => [
                'source' => 'safe_fallback',
                'confidence' => 0,
            ],
        ];
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
        AIResponse $aiResponse,
        array $flowContext = []
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

        /*
         * Recuperar los botones del nodo ai.enable usando
         * el contexto acumulado del diagnóstico anterior.
         *
         * De esta forma, "Reportar incidencia" y
         * "Contactar a Helpdesk" mantienen los datos
         * seleccionados antes de entrar al texto libre.
         */
        $aiFlowResponse = $this->flowService->handle(
            'ai.enable',
            $userName,
            $flowContext
        );

        $quickActions = is_array(
            $aiFlowResponse['quick_actions']
            ?? null
        )
            ? $aiFlowResponse['quick_actions']
            : [];

        /*
         * Evitar duplicar la acción de volver al menú
         * si el ResponseBuilder ya la incluyó.
         */
        $hasMenuAction = false;

        foreach ($quickActions as $quickAction) {
            if (
                ($quickAction['action'] ?? null) === 'flow'
                && ($quickAction['value'] ?? null) === 'menu.principal'
            ) {
                $hasMenuAction = true;
                break;
            }
        }

        if (! $hasMenuAction) {
            $quickActions[] = [
                'label' => 'Volver al menú',
                'action' => 'flow',
                'value' => 'menu.principal',
                'context' => [],
            ];
        }

        $response['quick_actions'] = $quickActions;

        $response['flow_context'] = $flowContext;

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
    | Preparar contexto acumulado del flujo
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