<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Services\Chatbot\AI\AIResponse;
use App\Services\Chatbot\AI\AIServiceInterface;
use App\Services\Chatbot\AI\FormPrefillExtractorService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Route;

class ChatbotService
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe los servicios responsables del reconocimiento de intenciones,
    | construcción de respuestas, estados de gestiones, inteligencia
    | artificial, contexto conversacional, flujos y prellenado.
    |
    */

    public function __construct(
        private readonly IntentRecognizerInterface $recognizer,
        private readonly ChatbotResponseBuilder $responseBuilder,
        private readonly GestionStatusService $gestionStatus,
        private readonly AIServiceInterface $aiService,
        private readonly ConversationContextService $contextService,
        private readonly ChatbotFlowService $flowService,
        private readonly FormPrefillExtractorService $prefillExtractor,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta tradicional
    |--------------------------------------------------------------------------
    |
    | Procesa una solicitud convencional utilizando el mismo flujo principal del servicio, pero sin enviar fragmentos progresivos al cliente.
    |
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
            onChunk: static function (string $chunk): void {
                /*
                 * Compatibilidad para solicitudes sin streaming.
                 */
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
    |
    | Coordina el procesamiento completo del chatbot, normalizando la entrada, resolviendo acciones, intenciones, diagnósticos, consultas de estado y respuestas mediante IA.
    |
    */

    public function handleStream(
        string $message,
        ?Authenticatable $user,
        callable $onChunk,
        ?string $action = null,
        bool $forceAI = false,
        array $flowContext = []
    ): array {
        $message = $this->prepareUserMessage($message);

        $flowContext = $this->prepareFlowContext(
            $flowContext
        );

        $action = is_string($action)
            ? trim($action)
            : null;

        $userName = $this->prepareUserName(
            data_get(
                $user,
                'nombre',
                config(
                    'chatbot.fallback_name',
                    'usuario'
                )
            )
        );

        $userId = $this->resolveUserId($user);

        /*
        |--------------------------------------------------------------------------
        | Acción interactiva
        |--------------------------------------------------------------------------
        |
        | Las acciones provenientes de botones no pasan por el reconocedor
        | de intenciones ni por Ollama.
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
        | Mensaje vacío
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
        | Mensaje enviado explícitamente a la IA
        |--------------------------------------------------------------------------
        */

        if ($forceAI) {
            /*
             * Los diagnósticos locales tienen prioridad sobre Ollama.
             * Esto evita llamadas innecesarias para problemas conocidos.
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
        | Consultar estado de gestiones
        |--------------------------------------------------------------------------
        */

        if ($intent->is('consultar_estado')) {
            return $this->buildEstadoResponse(
                userId: $userId,
                userName: $userName
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Menú y saludo
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
        */

        if ($intent->is('incidencia')) {
            /*
             * Cuando el usuario pide expresamente registrar una incidencia,
             * se utiliza la respuesta tradicional del portal.
             */
            if ($this->isDirectIncidenceCommand($message)) {
                $response = $this->responseBuilder->build(
                    $intent,
                    $userName,
                    $message
                );

                $response['flow_context'] = $flowContext;

                return $response;
            }

            /*
             * Si describe un problema conocido, dirigirlo al flujo
             * interactivo correspondiente.
             */
            $suggestedFlow = $this->detectProblemFlow(
                $message
            );

            if ($suggestedFlow !== null) {
                return $this->flowService->handle(
                    action: $suggestedFlow,
                    userName: $userName,
                    context: $flowContext
                ) ?? $this->flowService->handle(
                    action: 'problema.menu',
                    userName: $userName,
                    context: $flowContext
                ) ?? $this->flowService->menu(
                    $userName
                );
            }

            return $this->flowService->handle(
                action: 'problema.menu',
                userName: $userName,
                context: $flowContext
            ) ?? $this->flowService->menu(
                $userName
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Acciones conocidas del Portal TI
        |--------------------------------------------------------------------------
        */

        if (
            $intent->is('solicitud')
            || $intent->is('pase_menor_24h')
            || $intent->is('autorizacion_memorando')
            || $intent->is('cierre')
        ) {
            $response = $this->responseBuilder->build(
                $intent,
                $userName,
                $message
            );

            $response['flow_context'] = $flowContext;

            return $response;
        }

        /*
        |--------------------------------------------------------------------------
        | Texto libre
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
    |
    | Resuelve acciones enviadas desde botones, incluyendo consulta de estado, prellenado de formularios y navegación entre nodos del flujo.
    |
    */

    private function handleAction(
        string $action,
        ?int $userId,
        string $userName,
        array $flowContext = []
    ): array {
        $action = trim($action);

        /*
         * Consultar gestiones en base de datos.
         */
        if ($action === 'gestion.estado') {
            return $this->buildEstadoResponse(
                userId: $userId,
                userName: $userName
            );
        }

        /*
         * Preparar formularios usando la conversación acumulada.
         */
        if ($action === 'ai.prefill.incidencia') {
            return $this->buildFormPrefillResponse(
                type: 'incidencia',
                userName: $userName,
                flowContext: $flowContext
            );
        }

        if ($action === 'ai.prefill.solicitud') {
            return $this->buildFormPrefillResponse(
                type: 'solicitud',
                userName: $userName,
                flowContext: $flowContext
            );
        }

        /*
         * Volver al menú elimina el contexto del recorrido anterior.
         */
        if ($action === 'menu.principal') {
            $flowContext = [];
        }

        $response = $this->flowService->handle(
            action: $action,
            userName: $userName,
            context: $flowContext
        );

        if ($response !== null) {
            return $response;
        }

        return [
            'message' =>
                'No pude identificar esa opción. Selecciona nuevamente lo que necesitas.',

            'quick_actions' => [
                [
                    'label' => 'Mostrar menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                    'context' => [],
                ],
            ],

            'redirect' => null,

            'items' => [],

            'mode' => 'flow',

            'flow_context' => $flowContext,

            'intent' => [
                'name' => 'unknown_action',
                'score' => 0.0,
                'confidence' => 0.0,
                'action' => $action,
            ],

            'ai' => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Consultar Ollama
    |--------------------------------------------------------------------------
    |
    | Construye el contexto de la consulta, ejecuta la generación mediante el servicio de IA y aplica controles de disponibilidad, confianza y seguridad antes de devolver la respuesta.
    |
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
            intent: $intent,
            user: $user,
            userId: $userId,
            userName: $userName,
            flowContext: $flowContext
        );

        /*
        |--------------------------------------------------------------------------
        | Generación controlada
        |--------------------------------------------------------------------------
        |
        | La respuesta se acumula primero para poder validarla antes de
        | enviarla al navegador. Así evitamos mostrar instrucciones no
        | permitidas antes de detectarlas.
        |
        */

        $aiResponse = $this->aiService->stream(
            message: $message,
            context: $context,
            onChunk: static function (string $chunk): void {
                /*
                 * Los fragmentos se acumulan dentro de OllamaAIService.
                 */
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Ollama ocupado
        |--------------------------------------------------------------------------
        */

        if ($aiResponse->isBusy()) {
            return $this->buildSystemAIResponse(
                aiResponse: $aiResponse,
                intentName: 'ai_busy',
                flowContext: $flowContext
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ollama no disponible
        |--------------------------------------------------------------------------
        */

        if ($aiResponse->isFallback()) {
            if (
                $useLocalFallback
                && ! $intent->is('desconocido')
            ) {
                $fallback = $this->responseBuilder->build(
                    $intent,
                    $userName,
                    $message
                );

                $fallback['flow_context'] = $flowContext;

                return $fallback;
            }

            return $this->buildSystemAIResponse(
                aiResponse: $aiResponse,
                intentName: 'ai_fallback',
                flowContext: $flowContext
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Respuesta de baja confianza
        |--------------------------------------------------------------------------
        */

        if (
            $useLocalFallback
            && $aiResponse->confidence < 0.60
            && ! $intent->is('desconocido')
        ) {
            $fallback = $this->responseBuilder->build(
                $intent,
                $userName,
                $message
            );

            $fallback['flow_context'] = $flowContext;

            return $fallback;
        }

        $response = $this->buildAIResponse(
            originalIntent: $intent,
            userName: $userName,
            message: $message,
            aiResponse: $aiResponse,
            flowContext: $flowContext
        );

        /*
        |--------------------------------------------------------------------------
        | Validar contenido generado
        |--------------------------------------------------------------------------
        */

        if (
            ! $this->isSafeGeneratedMessage(
                $response['message'] ?? ''
            )
        ) {
            return $this->buildSafeAIFallback(
                userName: $userName,
                flowContext: $flowContext
            );
        }

        /*
         * Se entrega como un único fragmento una vez validada.
         */
        $safeMessage = trim(
            (string) ($response['message'] ?? '')
        );

        if ($safeMessage !== '') {
            $onChunk($safeMessage);
        }

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta del sistema para IA ocupada o no disponible
    |--------------------------------------------------------------------------
    |
    | Construye una respuesta uniforme para estados temporales del proveedor y conserva acciones que permiten reintentar o regresar al flujo principal.
    |
    */

    private function buildSystemAIResponse(
        AIResponse $aiResponse,
        string $intentName,
        array $flowContext = []
    ): array {
        $aiFlowResponse = $this->flowService->handle(
            action: 'ai.enable',
            userName: 'usuario',
            context: $flowContext
        );

        $quickActions = is_array(
            $aiFlowResponse['quick_actions'] ?? null
        )
            ? $aiFlowResponse['quick_actions']
            : [];

        if ($quickActions === []) {
            $quickActions = [
                [
                    'label' => 'Intentar nuevamente',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                    'context' => $flowContext,
                ],
                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                    'context' => [],
                ],
            ];
        }

        return [
            'message' => $aiResponse->message,

            'quick_actions' => $this->uniqueQuickActions(
                $quickActions
            ),

            'redirect' => null,

            'items' => [],

            'mode' => 'ai',

            'flow_context' => $flowContext,

            'intent' => [
                'name' => $intentName,
                'score' => 0.0,
                'confidence' => 0.0,
            ],

            'ai' => [
                'source' => $aiResponse->provider() ?? 'system',
                'confidence' => $aiResponse->confidence,
                'truncated' => $aiResponse->isTruncated(),
                'reused' => $aiResponse->isReused(),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Detectar diagnóstico local
    |--------------------------------------------------------------------------
    |
    | Evalúa diagnósticos configurados mediante palabras clave ponderadas y selecciona la coincidencia con mayor puntuación cuando supera el mínimo establecido.
    |
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

        foreach ($diagnostics as $key => $diagnostic) {
            if (! is_array($diagnostic)) {
                continue;
            }

            $keywords = $diagnostic['keywords'] ?? [];

            if (! is_array($keywords)) {
                continue;
            }

            $score = 0;
            $matched = [];

            foreach ($keywords as $keyword => $weight) {
                $normalizedKeyword = $this->normalizeText(
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

                    $matched[] = (string) $keyword;
                }
            }

            if ($score <= $bestScore) {
                continue;
            }

            $steps = is_array(
                $diagnostic['steps'] ?? null
            )
                ? $diagnostic['steps']
                : [];

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
                        $steps,
                        static fn (mixed $step): bool =>
                            is_scalar($step)
                            && trim((string) $step) !== ''
                    )
                ),

                'matched_keywords' => $matched,

                'score' => $score,
            ];
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
    |
    | Busca señales lingüísticas que indiquen que el mensaje corresponde a un problema técnico susceptible de diagnóstico.
    |
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
            'pantalla negra',
            'virus',
            'infectado',
            'sospechoso',
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
    |
    | Genera una respuesta local con pasos seguros, contexto de prellenado y acciones de seguimiento a partir del diagnóstico seleccionado.
    |
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

        if ($message === '') {
            $message = 'Parece que hay un problema técnico.';
        }

        $steps = array_slice(
            is_array($diagnostic['steps'] ?? null)
                ? $diagnostic['steps']
                : [],
            0,
            3
        );

        $validSteps = [];

        foreach ($steps as $step) {
            if (! is_scalar($step)) {
                continue;
            }

            $cleanStep = trim(
                strip_tags(
                    (string) $step
                )
            );

            if ($cleanStep === '') {
                continue;
            }

            $validSteps[] = mb_substr(
                $cleanStep,
                0,
                400
            );
        }

        if ($validSteps !== []) {
            $message .= PHP_EOL.PHP_EOL;

            foreach ($validSteps as $index => $step) {
                $message .= ($index + 1)
                    .'. '
                    .rtrim(
                        $step,
                        ". \t\n\r\0\x0B"
                    )
                    .'.'
                    .PHP_EOL;
            }

            $message = rtrim($message);
        }

        $message .=
            PHP_EOL
            .PHP_EOL
            .'Si el problema continúa, registra una incidencia para que el equipo de TI pueda revisarlo.';

        /*
         * Los datos sugeridos por el diagnóstico actual tienen prioridad.
         */
        $flowContext = array_replace(
            $flowContext,
            $this->diagnosticPrefill($key)
        );

        $flowContext = $this->appendPrefillSource(
            context: $flowContext,
            message: $message
        );

        $aiFlowResponse = $this->flowService->handle(
            action: 'ai.enable',
            userName: $userName,
            context: $flowContext
        );

        return [
            'message' => $message,

            'quick_actions' => is_array(
                $aiFlowResponse['quick_actions'] ?? null
            )
                ? $aiFlowResponse['quick_actions']
                : [],

            'redirect' => null,

            'items' => [],

            'mode' => 'ai',

            'flow_context' => $flowContext,

            'intent' => [
                'name' => 'diagnostico_local',
                'diagnostic' => $key,
                'score' => (int) (
                    $diagnostic['score']
                    ?? 0
                ),
                'confidence' => 1.0,
                'matched_keywords' => is_array(
                    $diagnostic['matched_keywords'] ?? null
                )
                    ? $diagnostic['matched_keywords']
                    : [],
            ],

            'ai' => [
                'source' => 'local_diagnostic',
                'confidence' => 1.0,
                'truncated' => false,
                'reused' => false,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Datos sugeridos para incidencia
    |--------------------------------------------------------------------------
    |
    | Asocia diagnósticos conocidos con valores iniciales que pueden utilizarse posteriormente para preparar un formulario de incidencia.
    |
    */

    private function diagnosticPrefill(
        string $diagnostic
    ): array {
        return match ($diagnostic) {
            'internet' => [
                'titulo' => 'Problema con internet o WiFi',
                'equipo' => 'Red / WiFi',
                'tipo_gestion' => 'incidencia',
            ],

            'correo' => [
                'titulo' => 'Problema con Outlook o correo',
                'equipo' => 'Outlook / Correo corporativo',
                'tipo_gestion' => 'incidencia',
            ],

            'equipo_lento' => [
                'titulo' => 'Equipo lento o congelado',
                'equipo' => 'Computadora',
                'tipo_gestion' => 'incidencia',
            ],

            'pc_no_enciende' => [
                'titulo' => 'Equipo no enciende',
                'equipo' => 'Computadora',
                'tipo_gestion' => 'incidencia',
            ],

            'impresora' => [
                'titulo' => 'Problema con impresora',
                'equipo' => 'Impresora',
                'tipo_gestion' => 'incidencia',
            ],

            'sistema' => [
                'titulo' => 'Problema con sistema o aplicación',
                'equipo' => 'Sistema / Aplicación',
                'tipo_gestion' => 'incidencia',
            ],

            'perifericos',
            'periferico' => [
                'titulo' => 'Problema con periférico',
                'equipo' => 'Periférico',
                'tipo_gestion' => 'incidencia',
            ],

            'virus' => [
                'titulo' => 'Comportamiento sospechoso en el equipo',
                'equipo' => 'Computadora',
                'tipo_gestion' => 'incidencia',
            ],

            default => [],
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Validar respuesta generada
    |--------------------------------------------------------------------------
    |
    | Aplica restricciones de longitud, contenido y cantidad de pasos para impedir que la IA entregue instrucciones administrativas o potencialmente inseguras.
    |
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

        if (mb_strlen($message) > 1800) {
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
            'abre la consola',
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
            'deshabilita el antivirus',
            'desactiva el firewall',
            'desactivar el firewall',
            'deshabilita el firewall',
            'registro de windows',
            'servicios de windows',
            'como administrador',
            'ejecutar como administrador',
            'formatea el equipo',
            'formatear el equipo',
            'reinstala windows',
            'reinstalar windows',
        ];

        foreach ($forbiddenPatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return false;
            }
        }

        /*
         * Bloquear comandos comunes presentados como código.
         */
        if (
            preg_match(
                '/```|`(?:ipconfig|ping|netsh|sfc|chkdsk|reg|sc|taskkill|shutdown)\b/iu',
                $message
            ) === 1
        ) {
            return false;
        }

        /*
         * Máximo tres pasos numerados.
         */
        preg_match_all(
            '/(?:^|\R)\s*\d+[\.)]\s+/u',
            $message,
            $matches
        );

        if (
            count(
                $matches[0] ?? []
            ) > 3
        ) {
            return false;
        }

        if (
            preg_match_all(
                '/\*\*paso\s+\d+/iu',
                $message
            ) > 3
        ) {
            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta segura cuando Ollama incumple las reglas
    |--------------------------------------------------------------------------
    |
    | Genera una respuesta alternativa cuando el contenido producido por la IA no supera las validaciones de seguridad establecidas.
    |
    */

    private function buildSafeAIFallback(
        string $userName,
        array $flowContext = []
    ): array {
        $aiFlowResponse = $this->flowService->handle(
            action: 'ai.enable',
            userName: $userName,
            context: $flowContext
        );

        return [
            'message' =>
                'No pude generar una recomendación suficientemente clara y segura. Describe brevemente qué está fallando o registra una incidencia para que el equipo de TI pueda revisarlo.',

            'quick_actions' => is_array(
                $aiFlowResponse['quick_actions'] ?? null
            )
                ? $aiFlowResponse['quick_actions']
                : [],

            'redirect' => null,

            'items' => [],

            'mode' => 'ai',

            'flow_context' => $flowContext,

            'intent' => [
                'name' => 'ai_safe_fallback',
                'score' => 0.0,
                'confidence' => 0.0,
            ],

            'ai' => [
                'source' => 'safe_fallback',
                'confidence' => 0.0,
                'truncated' => false,
                'reused' => false,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Detectar flujo técnico
    |--------------------------------------------------------------------------
    |
    | Relaciona términos del mensaje con flujos guiados para problemas frecuentes como conectividad, correo, lentitud, encendido, impresión, aplicaciones y periféricos.
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
                'camara',
                'microfono',
            ],
        ];

        foreach ($flows as $flow => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
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
    |
    | Identifica expresiones explícitas mediante las cuales el usuario solicita registrar o crear directamente una incidencia.
    |
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
            if (str_contains($message, $command)) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Construir contexto para Ollama
    |--------------------------------------------------------------------------
    |
    | Prepara el contexto enviado al modelo utilizando intención, usuario, historial reciente y datos acumulados durante el flujo actual.
    |
    */

    private function buildAIContext(
        IntentResult $intent,
        ?Authenticatable $user,
        ?int $userId,
        string $userName,
        array $flowContext = []
    ): array {
        $historyLimit = max(
            0,
            (int) config(
                'chatbot.ai.history_limit',
                2
            )
        );

        $history = $this->contextService->getRecent(
            $userId,
            $historyLimit
        );

        $roleName = data_get(
            $user,
            'rol.nombre'
        );

        $roleName = is_scalar($roleName)
            ? trim((string) $roleName)
            : '';

        $managementType =
            $flowContext['management_type']
            ?? $flowContext['tipo_gestion']
            ?? null;

        return [
            'intent' => $intent->intent,

            'usuario' => $this->prepareUserName(
                $userName
            ),

            'rol' => $roleName !== ''
                ? $roleName
                : null,

            'history' => is_array($history)
                ? $history
                : [],

            'purpose' => 'chat',

            /*
             * Delimita la caché de deduplicación por usuario.
             */
            'user_id' => $userId,

            /*
             * Contexto opcional del recorrido actual.
             */
            'flow' => $managementType,

            'management_type' => $managementType,

            'tipo_gestion' => $managementType,

            'step' => $flowContext['step'] ?? null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Construir respuesta final de IA
    |--------------------------------------------------------------------------
    |
    | Integra la respuesta de IA con el constructor general, conserva el contexto de conversación y agrega acciones de menú y prellenado cuando corresponda.
    |
    */

    private function buildAIResponse(
        IntentResult $originalIntent,
        string $userName,
        string $message,
        AIResponse $aiResponse,
        array $flowContext = []
    ): array {
        /*
         * Conservar el texto aportado por el usuario para poder preparar
         * posteriormente una incidencia o solicitud.
         */
        $flowContext = $this->appendPrefillSource(
            context: $flowContext,
            message: $message
        );

        $response = $this->responseBuilder->build(
            new IntentResult(
                intent: 'ai',

                score: $originalIntent->score,

                matchedKeywords: $originalIntent->matchedKeywords,

                confidence: $aiResponse->confidence
            ),

            $userName,

            $message,

            $aiResponse
        );

        $response['mode'] = 'ai';

        $aiFlowResponse = $this->flowService->handle(
            action: 'ai.enable',
            userName: $userName,
            context: $flowContext
        );

        $quickActions = is_array(
            $aiFlowResponse['quick_actions'] ?? null
        )
            ? $aiFlowResponse['quick_actions']
            : [];

        /*
         * Garantizar un botón para regresar al menú.
         */
        $hasMenuAction = false;

        foreach ($quickActions as $quickAction) {
            if (
                is_array($quickAction)
                && ($quickAction['action'] ?? null) === 'flow'
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

        foreach (
            $this->buildPrefillActions(
                originalIntent: $originalIntent,
                message: $message,
                flowContext: $flowContext
            ) as $prefillAction
        ) {
            $quickActions[] = $prefillAction;
        }

        $response['quick_actions'] = $this->uniqueQuickActions(
            $quickActions
        );

        $response['flow_context'] = $flowContext;

        if (! is_array($response['ai'] ?? null)) {
            $response['ai'] = [];
        }

        $response['ai'] = array_merge(
            $response['ai'],
            [
                'source' => $aiResponse->provider() ?? 'ollama',
                'confidence' => $aiResponse->confidence,
                'truncated' => $aiResponse->isTruncated(),
                'reused' => $aiResponse->isReused(),
            ]
        );

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar formulario desde la conversación
    |--------------------------------------------------------------------------
    |
    | Utiliza el contenido acumulado de la conversación para extraer datos estructurados y preparar una incidencia o solicitud para revisión del usuario.
    |
    */

    private function buildFormPrefillResponse(
        string $type,
        string $userName,
        array $flowContext = []
    ): array {
        $type = $type === 'solicitud'
            ? 'solicitud'
            : 'incidencia';

        $source = trim(
            (string) (
                $flowContext['prefill_source']
                ?? ''
            )
        );

        if ($source === '') {
            return $this->buildPrefillFailureResponse(
                type: $type,
                userName: $userName,
                flowContext: $flowContext,
                message:
                    'Primero describe brevemente lo que necesitas o el problema que estás teniendo.'
            );
        }

        $result = $this->prefillExtractor->extract(
            message: $source,
            forcedType: $type,
            existingContext: $flowContext
        );

        $fields = is_array(
            $result['campos'] ?? null
        )
            ? $result['campos']
            : [];

        $reason = is_string(
            $result['reason'] ?? null
        )
            ? $result['reason']
            : null;

        /*
        |--------------------------------------------------------------------------
        | Fallos específicos del extractor
        |--------------------------------------------------------------------------
        */

        if ($reason === 'ollama_busy') {
            return $this->buildPrefillFailureResponse(
                type: $type,
                userName: $userName,
                flowContext: $flowContext,
                message:
                    'El asistente está procesando otra consulta. Espera unos segundos y vuelve a preparar el formulario.'
            );
        }

        if ($reason === 'ollama_unavailable') {
            return $this->buildPrefillFailureResponse(
                type: $type,
                userName: $userName,
                flowContext: $flowContext,
                message:
                    'No pude analizar la conversación en este momento. Puedes abrir el formulario y completar los datos manualmente.'
            );
        }

        if (
            $reason === 'truncated_response'
            || $reason === 'invalid_json_response'
            || $reason === 'invalid_ollama_payload'
        ) {
            return $this->buildPrefillFailureResponse(
                type: $type,
                userName: $userName,
                flowContext: $flowContext,
                message:
                    'No pude completar correctamente la preparación automática. Describe el caso de forma más breve y vuelve a intentarlo.'
            );
        }

        if (
            ! ($result['success'] ?? false)
            || $fields === []
        ) {
            return $this->buildPrefillFailureResponse(
                type: $type,
                userName: $userName,
                flowContext: $flowContext,
                message:
                    'No pude identificar suficientes datos para preparar el formulario. Describe el caso con un poco más de detalle y vuelve a intentarlo.'
            );
        }

        $redirectAction = $this->buildPrefillRedirectAction(
            type: $type,
            fields: $fields
        );

        if ($redirectAction === null) {
            return $this->buildPrefillFailureResponse(
                type: $type,
                userName: $userName,
                flowContext: $flowContext,
                message:
                    'Identifiqué la información, pero no pude construir el acceso al formulario correspondiente.'
            );
        }

        /*
         * Los campos recién extraídos reemplazan sugerencias anteriores.
         */
        $cleanContext = array_replace(
            $flowContext,
            $fields,
            [
                'tipo_gestion' => $type,
                'management_type' => $type,
            ]
        );

        $cleanContext = $this->prepareFlowContext(
            $cleanContext
        );

        $isTruncated = (bool) (
            $result['truncated']
            ?? false
        );

        $summaryMessage = $this->buildPrefillSummaryMessage(
            type: $type,
            fields: $fields
        );

        if ($isTruncated) {
            $summaryMessage .=
                PHP_EOL
                .PHP_EOL
                .'Algunos datos pudieron haberse recortado por longitud. Revisa cada campo con atención antes de continuar.';
        }

        return [
            'message' => $summaryMessage,

            'quick_actions' => [
                $redirectAction,

                [
                    'label' => 'Seguir conversando',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                    'context' => $cleanContext,
                ],

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                    'context' => [],
                ],
            ],

            'redirect' => null,

            'items' => [],

            'mode' => 'ai',

            'flow_context' => $cleanContext,

            'intent' => [
                'name' => 'ai_form_prefill',
                'type' => $type,
                'score' => 1.0,
                'confidence' => (float) (
                    $result['confidence']
                    ?? 0
                ),
            ],

            'ai' => [
                'source' =>
                    $result['source']
                    ?? 'ollama_form_prefill',

                'confidence' => (float) (
                    $result['confidence']
                    ?? 0
                ),

                'truncated' => $isTruncated,

                'reused' => false,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Fallo al preparar formulario
    |--------------------------------------------------------------------------
    |
    | Construye una respuesta alternativa cuando no fue posible preparar automáticamente el formulario e incluye la opción de completarlo manualmente.
    |
    */

    private function buildPrefillFailureResponse(
        string $type,
        string $userName,
        array $flowContext,
        string $message
    ): array {
        $aiFlowResponse = $this->flowService->handle(
            action: 'ai.enable',
            userName: $userName,
            context: $flowContext
        );

        $quickActions = is_array(
            $aiFlowResponse['quick_actions'] ?? null
        )
            ? $aiFlowResponse['quick_actions']
            : [];

        $manualAction = $this->buildManualFormAction(
            $type
        );

        if ($manualAction !== null) {
            $quickActions[] = $manualAction;
        }

        $quickActions[] = [
            'label' => 'Volver al menú',
            'action' => 'flow',
            'value' => 'menu.principal',
            'context' => [],
        ];

        return [
            'message' => $message,

            'quick_actions' => $this->uniqueQuickActions(
                $quickActions
            ),

            'redirect' => null,

            'items' => [],

            'mode' => 'ai',

            'flow_context' => $flowContext,

            'intent' => [
                'name' => 'ai_form_prefill_failed',
                'type' => $type,
                'score' => 0.0,
                'confidence' => 0.0,
            ],

            'ai' => [
                'source' => 'form_prefill_fallback',
                'confidence' => 0.0,
                'truncated' => false,
                'reused' => false,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Botón de formulario preparado
    |--------------------------------------------------------------------------
    |
    | Genera una acción de redirección hacia el formulario correspondiente incluyendo únicamente los campos extraídos permitidos para el módulo.
    |
    */

    private function buildPrefillRedirectAction(
        string $type,
        array $fields
    ): ?array {
        $module = $type === 'solicitud'
            ? 'solicitud'
            : 'incidencia';

        $routeName = config(
            "chatbot.modules.{$module}.create"
        );

        if (
            ! is_string($routeName)
            || trim($routeName) === ''
            || ! Route::has($routeName)
        ) {
            return null;
        }

        $filteredFields = $this->filterFieldsForModule(
            module: $module,
            fields: $fields
        );

        return [
            'label' => $module === 'incidencia'
                ? 'Revisar incidencia'
                : 'Revisar solicitud',

            'description' =>
                'Abre el formulario con los datos detectados para que puedas revisarlos antes de enviarlo.',

            'icon' => $module === 'incidencia'
                ? 'triangle-alert'
                : 'clipboard-list',

            'variant' => 'ai',

            'action' => 'redirect',

            'url' => empty($filteredFields)
                ? route($routeName)
                : route(
                    $routeName,
                    $filteredFields
                ),

            'context' => $filteredFields,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Botón de formulario manual
    |--------------------------------------------------------------------------
    |
    | Construye una acción de acceso directo al formulario sin datos automáticos cuando el prellenado no se encuentra disponible.
    |
    */

    private function buildManualFormAction(
        string $type
    ): ?array {
        $module = $type === 'solicitud'
            ? 'solicitud'
            : 'incidencia';

        $routeName = config(
            "chatbot.modules.{$module}.create"
        );

        if (
            ! is_string($routeName)
            || trim($routeName) === ''
            || ! Route::has($routeName)
        ) {
            return null;
        }

        return [
            'label' => $module === 'incidencia'
                ? 'Abrir incidencia'
                : 'Abrir solicitud',

            'description' =>
                'Abre el formulario para completar los datos manualmente.',

            'icon' => $module === 'incidencia'
                ? 'triangle-alert'
                : 'clipboard-list',

            'variant' => 'default',

            'action' => 'redirect',

            'url' => route($routeName),

            'context' => [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Filtrar campos por módulo
    |--------------------------------------------------------------------------
    |
    | Limita los campos extraídos a aquellos admitidos específicamente por los formularios de incidencias o solicitudes.
    |
    */

    private function filterFieldsForModule(
        string $module,
        array $fields
    ): array {
        $allowedFields = match ($module) {
            'incidencia' => [
                'titulo',
                'descripcion',
                'tiempo_problema',
                'afectacion',
                'equipo',
                'ubicacion',
            ],

            'solicitud' => [
                'categoria',
                'asunto',
                'descripcion',
                'tipo_equipo',
                'accesorio',
                'programa',
                'sistema',
                'tipo_acceso',
                'justificacion',
                'usuario_afectado',
                'equipo_actual',
                'motivo_cambio',
            ],

            default => [],
        };

        if ($allowedFields === []) {
            return [];
        }

        return array_intersect_key(
            $fields,
            array_flip($allowedFields)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resumen de datos preparados
    |--------------------------------------------------------------------------
    |
    | Genera una vista resumida de los principales campos detectados para que el usuario pueda revisarlos antes de abrir el formulario.
    |
    */

    private function buildPrefillSummaryMessage(
        string $type,
        array $fields
    ): string {
        $managementName = $type === 'solicitud'
            ? 'solicitud'
            : 'incidencia';

        $labels = [
            'titulo' => 'Título',
            'asunto' => 'Asunto',
            'descripcion' => 'Descripción',
            'tiempo_problema' => 'Tiempo del problema',
            'afectacion' => 'Afectación',
            'equipo' => 'Equipo o servicio',
            'ubicacion' => 'Ubicación',
            'categoria' => 'Categoría',
            'tipo_equipo' => 'Tipo de equipo',
            'accesorio' => 'Accesorio',
            'programa' => 'Programa',
            'sistema' => 'Sistema',
            'tipo_acceso' => 'Tipo de acceso',
            'justificacion' => 'Justificación',
            'usuario_afectado' => 'Usuario afectado',
            'equipo_actual' => 'Equipo actual',
            'motivo_cambio' => 'Motivo del cambio',
        ];

        $lines = [
            "Preparé los datos para tu {$managementName}. Revísalos antes de registrar la gestión.",
        ];

        $shown = 0;

        foreach ($labels as $key => $label) {
            if ($shown >= 8) {
                break;
            }

            if (! array_key_exists($key, $fields)) {
                continue;
            }

            if (! is_scalar($fields[$key])) {
                continue;
            }

            $value = trim(
                strip_tags(
                    (string) $fields[$key]
                )
            );

            if ($value === '') {
                continue;
            }

            $value = preg_replace(
                '/\s+/u',
                ' ',
                $value
            ) ?? $value;

            $value = mb_substr(
                $value,
                0,
                300
            );

            $lines[] = "• {$label}: {$value}";

            $shown++;
        }

        return implode(
            PHP_EOL,
            $lines
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Acumular texto para prellenado
    |--------------------------------------------------------------------------
    |
    | Conserva fragmentos relevantes de la conversación en el contexto para utilizarlos posteriormente durante la extracción automática de datos.
    |
    */

    private function appendPrefillSource(
        array $context,
        string $message
    ): array {
        $message = trim(
            strip_tags($message)
        );

        if ($message === '') {
            return $context;
        }

        $message = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $message
        ) ?? $message;

        $message = mb_substr(
            $message,
            0,
            1500
        );

        $previous = trim(
            (string) (
                $context['prefill_source']
                ?? ''
            )
        );

        /*
         * Evitar agregar exactamente el mismo mensaje dos veces.
         */
        if (
            $previous !== ''
            && str_ends_with(
                $previous,
                $message
            )
        ) {
            return $context;
        }

        $combined = $previous === ''
            ? $message
            : $previous.PHP_EOL.$message;

        $context['prefill_source'] = mb_substr(
            $combined,
            -3000
        );

        return $context;
    }

    /*
    |--------------------------------------------------------------------------
    | Construir acciones de prellenado
    |--------------------------------------------------------------------------
    |
    | Genera botones para preparar incidencias o solicitudes cuando el mensaje y el contexto indican que existe información suficiente para intentarlo.
    |
    */

    private function buildPrefillActions(
        IntentResult $originalIntent,
        string $message,
        array $flowContext
    ): array {
        $types = $this->detectPrefillTypes(
            originalIntent: $originalIntent,
            message: $message,
            flowContext: $flowContext
        );

        $actions = [];

        if (in_array('incidencia', $types, true)) {
            $actions[] = [
                'label' => 'Preparar incidencia',

                'description' =>
                    'Extrae los datos de la conversación y prepara el formulario para revisión.',

                'icon' => 'wand-sparkles',

                'variant' => 'ai',

                'action' => 'flow',

                'value' => 'ai.prefill.incidencia',

                'context' => $flowContext,
            ];
        }

        if (in_array('solicitud', $types, true)) {
            $actions[] = [
                'label' => 'Preparar solicitud',

                'description' =>
                    'Extrae los datos de la conversación y prepara el formulario para revisión.',

                'icon' => 'wand-sparkles',

                'variant' => 'ai',

                'action' => 'flow',

                'value' => 'ai.prefill.solicitud',

                'context' => $flowContext,
            ];
        }

        return $actions;
    }

    /*
    |--------------------------------------------------------------------------
    | Detectar tipos de prellenado
    |--------------------------------------------------------------------------
    |
    | Determina si el contenido de la conversación corresponde a una incidencia, solicitud o ambas opciones mediante intención, señales textuales y contexto acumulado.
    |
    */

    private function detectPrefillTypes(
        IntentResult $originalIntent,
        string $message,
        array $flowContext
    ): array {
        $types = [];

        if (
            $originalIntent->is('incidencia')
            || $this->looksLikeDiagnosticRequest($message)
            || $this->detectProblemFlow($message) !== null
            || isset($flowContext['tiempo_problema'])
            || isset($flowContext['afectacion'])
            || (
                $flowContext['tipo_gestion']
                ?? null
            ) === 'incidencia'
        ) {
            $types[] = 'incidencia';
        }

        $normalized = $this->normalizeText(
            $message
        );

        $requestSignals = [
            'necesito instalar',
            'quiero instalar',
            'solicito',
            'solicitud',
            'necesito acceso',
            'quiero acceso',
            'crear cuenta',
            'cuenta de correo',
            'necesito vpn',
            'cambio de equipo',
            'necesito un equipo',
            'necesito una impresora',
            'requiero',
            'quiero solicitar',
        ];

        if ($originalIntent->is('solicitud')) {
            $types[] = 'solicitud';
        } else {
            foreach ($requestSignals as $signal) {
                if (str_contains($normalized, $signal)) {
                    $types[] = 'solicitud';

                    break;
                }
            }
        }

        if (
            isset($flowContext['categoria'])
            || isset($flowContext['programa'])
            || isset($flowContext['sistema'])
            || isset($flowContext['tipo_acceso'])
            || (
                $flowContext['tipo_gestion']
                ?? null
            ) === 'solicitud'
        ) {
            $types[] = 'solicitud';
        }

        return array_values(
            array_unique($types)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar botones duplicados
    |--------------------------------------------------------------------------
    |
    | Elimina acciones rápidas repetidas comparando sus propiedades principales antes de devolverlas al frontend.
    |
    */

    private function uniqueQuickActions(
        array $actions
    ): array {
        $unique = [];
        $seen = [];

        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            $key = implode(
                '|',
                [
                    (string) (
                        $action['action']
                        ?? ''
                    ),

                    (string) (
                        $action['value']
                        ?? ''
                    ),

                    (string) (
                        $action['url']
                        ?? ''
                    ),

                    (string) (
                        $action['label']
                        ?? ''
                    ),
                ]
            );

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            $unique[] = $action;
        }

        return $unique;
    }

    /*
    |--------------------------------------------------------------------------
    | Estado de gestiones
    |--------------------------------------------------------------------------
    |
    | Consulta el resumen de gestiones asociadas al usuario y construye una respuesta con totales, estados y elementos recientes.
    |
    */

    private function buildEstadoResponse(
        ?int $userId,
        string $userName
    ): array {
        if ($userId === null || $userId <= 0) {
            return [
                'message' =>
                    'Necesitas iniciar sesión para consultar tus gestiones.',

                'quick_actions' => [
                    [
                        'label' => 'Volver al menú',
                        'action' => 'flow',
                        'value' => 'menu.principal',
                        'context' => [],
                    ],
                ],

                'redirect' => null,

                'items' => [],

                'mode' => 'flow',

                'flow_context' => [],

                'intent' => [
                    'name' => 'consultar_estado',
                    'score' => 1.0,
                    'confidence' => 1.0,
                ],

                'ai' => null,
            ];
        }

        $summary = $this->gestionStatus->getSummaryFor(
            $userId,
            5
        );

        $items = is_array(
            $summary['items'] ?? null
        )
            ? $summary['items']
            : [];

        $total = max(
            0,
            (int) (
                $summary['total']
                ?? 0
            )
        );

        $abiertas = max(
            0,
            (int) (
                $summary['abiertas']
                ?? 0
            )
        );

        $enProceso = max(
            0,
            (int) (
                $summary['en_proceso']
                ?? 0
            )
        );

        $finalizadas = max(
            0,
            (int) (
                $summary['finalizadas']
                ?? 0
            )
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
                        'context' => [],
                    ],

                    [
                        'label' => 'Crear solicitud',
                        'action' => 'flow',
                        'value' => 'solicitud.menu',
                        'context' => [],
                    ],

                    [
                        'label' => 'Volver al menú',
                        'action' => 'flow',
                        'value' => 'menu.principal',
                        'context' => [],
                    ],
                ],

                'redirect' => null,

                'items' => [],

                'mode' => 'flow',

                'flow_context' => [],

                'intent' => [
                    'name' => 'consultar_estado',
                    'score' => 1.0,
                    'confidence' => 1.0,
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
            .'.'
            .PHP_EOL
            .PHP_EOL;

        if ($abiertas > 0) {
            $responseMessage .=
                "• Abiertas o pendientes: {$abiertas}"
                .PHP_EOL;
        }

        if ($enProceso > 0) {
            $responseMessage .=
                "• En proceso: {$enProceso}"
                .PHP_EOL;
        }

        if ($finalizadas > 0) {
            $responseMessage .=
                "• Finalizadas: {$finalizadas}"
                .PHP_EOL;
        }

        if ($items !== []) {
            $responseMessage .=
                PHP_EOL
                .'Te muestro las gestiones más recientes.';
        }

        return [
            'message' => trim(
                $responseMessage
            ),

            'quick_actions' => [
                [
                    'label' => 'Actualizar',
                    'action' => 'status',
                    'value' => 'gestion.estado',
                    'context' => [],
                ],

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                    'context' => [],
                ],
            ],

            'redirect' => null,

            'items' => $items,

            'mode' => 'flow',

            'flow_context' => [],

            'intent' => [
                'name' => 'consultar_estado',
                'score' => 1.0,
                'confidence' => 1.0,
            ],

            'ai' => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar contexto acumulado
    |--------------------------------------------------------------------------
    |
    | Filtra, limpia y limita los valores almacenados durante el recorrido del chatbot para conservar únicamente campos permitidos.
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

            'prefill_source',

            'correo',
            'tipo_gestion',
            'management_type',
            'step',
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
                strip_tags(
                    (string) $value
                )
            );

            if ($value === '') {
                continue;
            }

            $value = preg_replace(
                '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
                '',
                $value
            ) ?? $value;

            $limit = match ($key) {
                'prefill_source' => 3000,

                'descripcion' => 2000,

                'justificacion',
                'motivo_cambio' => 1000,

                default => 500,
            };

            $prepared[$key] = mb_substr(
                $value,
                0,
                $limit
            );
        }

        return $prepared;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar nombre del usuario
    |--------------------------------------------------------------------------
    |
    | Normaliza el nombre mostrado por el chatbot y utiliza el valor de respaldo configurado cuando el dato recibido no es válido.
    |
    */

    private function prepareUserName(
        mixed $userName
    ): string {
        $fallback = trim(
            (string) config(
                'chatbot.fallback_name',
                'usuario'
            )
        );

        if ($fallback === '') {
            $fallback = 'usuario';
        }

        if (! is_scalar($userName)) {
            return $fallback;
        }

        $userName = trim(
            strip_tags(
                (string) $userName
            )
        );

        $userName = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            ' ',
            $userName
        ) ?? $userName;

        $userName = preg_replace(
            '/\s+/u',
            ' ',
            $userName
        ) ?? $userName;

        $userName = mb_substr(
            trim($userName),
            0,
            150
        );

        return $userName !== ''
            ? $userName
            : $fallback;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar mensaje del usuario
    |--------------------------------------------------------------------------
    |
    | Limpia caracteres de control y limita la longitud del mensaje antes de procesarlo mediante reconocimiento, flujos o inteligencia artificial.
    |
    */

    private function prepareUserMessage(
        string $message
    ): string {
        $message = trim($message);

        if ($message === '') {
            return '';
        }

        $message = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $message
        ) ?? $message;

        $maximumLength = max(
            100,
            (int) config(
                'chatbot.message_max_length',
                500
            )
        );

        return mb_substr(
            trim($message),
            0,
            $maximumLength
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener identificador del usuario
    |--------------------------------------------------------------------------
    |
    | Extrae y valida el identificador autenticable del usuario para utilizarlo en consultas de historial, estado y contexto.
    |
    */

    private function resolveUserId(
        ?Authenticatable $user
    ): ?int {
        if ($user === null) {
            return null;
        }

        $identifier = $user->getAuthIdentifier();

        if (! is_numeric($identifier)) {
            return null;
        }

        $userId = (int) $identifier;

        return $userId > 0
            ? $userId
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar texto
    |--------------------------------------------------------------------------
    |
    | Convierte el texto a minúsculas, elimina variaciones de caracteres acentuados y normaliza espacios para facilitar comparaciones internas.
    |
    */

    private function normalizeText(
        string $text
    ): string {
        $text = mb_strtolower(
            trim($text),
            'UTF-8'
        );

        $text = strtr(
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

        return preg_replace(
            '/\s+/u',
            ' ',
            $text
        ) ?? $text;
    }

    /*
    |--------------------------------------------------------------------------
    | Singular y plural
    |--------------------------------------------------------------------------
    |
    | Selecciona la forma singular o plural de una expresión según la cantidad recibida.
    |
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