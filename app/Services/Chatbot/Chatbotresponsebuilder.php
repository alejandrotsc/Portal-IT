<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Services\Chatbot\AI\AIResponse;
use App\Services\Chatbot\Diagnostics\DiagnosticEngine;
use Illuminate\Support\Facades\Route;

class ChatbotResponseBuilder
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Recibe el motor de diagnósticos utilizado para generar respuestas
    | locales antes de recurrir a otros mecanismos de atención.
    |
    */

    public function __construct(
        private readonly DiagnosticEngine $diagnosticEngine
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Construir respuesta principal
    |--------------------------------------------------------------------------
    |
    | Genera la respuesta final del chatbot a partir de la intención
    | detectada, el mensaje del usuario y, cuando corresponde, una
    | respuesta producida por el servicio de inteligencia artificial.
    |
    */

    public function build(
        IntentResult $intent,
        string $userName,
        string $message = '',
        ?AIResponse $aiResponse = null
    ): array {
        $userName = $this->prepareText(
            value: $userName,
            limit: 150,
            fallback: 'usuario'
        );

        $message = $this->prepareLongText(
            value: $message,
            limit: max(
                100,
                (int) config(
                    'chatbot.message_max_length',
                    500
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Diagnóstico basado en reglas
        |--------------------------------------------------------------------------
        |
        | Se conserva por compatibilidad con el DiagnosticEngine existente.
        | ChatbotService también cuenta con diagnósticos locales previos a IA,
        | pero esta comprobación sigue siendo útil para las respuestas locales
        | construidas directamente mediante este servicio.
        |
        */

        if ($intent->is('incidencia') && $message !== '') {
            $diagnostic = $this->diagnosticEngine->diagnose(
                $message
            );

            if (is_array($diagnostic) && $diagnostic !== []) {
                return $this->appendIntent(
                    response: $this->buildDiagnosticResponse(
                        diagnostic: $diagnostic,
                        userName: $userName
                    ),
                    intent: $intent
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Respuesta por intención
        |--------------------------------------------------------------------------
        */

        $response = match ($intent->intent) {
            'incidencia' => $this->forModule(
                key: 'incidencia',
                message:
                    "Entiendo, {$userName}. Puedes registrar una incidencia "
                    .'para que el equipo de TI revise el problema.'
            ),

            'solicitud' => $this->forModule(
                key: 'solicitud',
                message:
                    "Perfecto, {$userName}. Puedes crear una solicitud para "
                    .'instalaciones, accesos, equipos, cuentas u otros servicios tecnológicos.'
            ),

            'pase_menor_24h' => $this->forModule(
                key: 'pase_menor_24h',
                message:
                    'Para un acceso temporal menor a 24 horas debes gestionar un pase temporal.'
            ),

            'autorizacion_memorando' => $this->forModule(
                key: 'autorizacion_memorando',
                message:
                    'Para un acceso mayor a 24 horas debes gestionar una autorización mediante memorando.'
            ),

            'saludo' => $this->baseResponse(
                message: "Hola, {$userName}. ¿En qué puedo ayudarte?",
                quickActions: $this->defaultQuickActions(),
                mode: 'flow'
            ),

            'cierre' => $this->baseResponse(
                message:
                    "Excelente, {$userName}. Me alegra saber que el problema quedó resuelto.",
                quickActions: $this->defaultQuickActions(),
                mode: 'flow'
            ),

            'menu' => $this->baseResponse(
                message: 'Estas son las opciones disponibles:',
                quickActions: $this->mainMenuActions(),
                mode: 'flow'
            ),

            'ai' => $this->buildAIResponse(
                $aiResponse
            ),

            default => $this->baseResponse(
                message:
                    "No estoy seguro de haber entendido tu solicitud, {$userName}. "
                    .'Selecciona una opción para continuar.',
                quickActions: $this->defaultQuickActions(),
                mode: 'flow'
            ),
        };

        return $this->appendIntent(
            response: $response,
            intent: $intent
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta base
    |--------------------------------------------------------------------------
    |
    | Construye la estructura común utilizada por todas las respuestas
    | del chatbot y normaliza las acciones rápidas antes de devolverlas.
    |
    */

    private function baseResponse(
        string $message,
        array $quickActions = [],
        ?array $redirect = null,
        array $items = [],
        string $mode = 'flow',
        array $flowContext = [],
        ?array $ai = null
    ): array {
        return [
            'message' => trim($message),

            'quick_actions' => $this->uniqueQuickActions(
                $quickActions
            ),

            'redirect' => $redirect,

            'items' => $items,

            'mode' => $mode,

            'flow_context' => $flowContext,

            'ai' => $ai,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta de diagnóstico
    |--------------------------------------------------------------------------
    |
    | Construye una respuesta guiada a partir del diagnóstico basado en
    | reglas, limita los pasos mostrados y ofrece acciones para continuar
    | con la atención o registrar una incidencia.
    |
    */

    private function buildDiagnosticResponse(
        array $diagnostic,
        string $userName
    ): array {
        $diagnosticMessage = $this->prepareLongText(
            value:
                $diagnostic['message']
                ?? 'Vamos a revisar el problema.',
            limit: 800
        );

        if ($diagnosticMessage === '') {
            $diagnosticMessage = 'Vamos a revisar el problema.';
        }

        $steps = $this->prepareDiagnosticSteps(
            $diagnostic['steps'] ?? []
        );

        $message = "{$userName}, {$diagnosticMessage}";

        if ($steps !== []) {
            $message .= PHP_EOL.PHP_EOL.'Puedes probar:';

            foreach ($steps as $step) {
                $message .= PHP_EOL.'• '.$step;
            }
        }

        $message .=
            PHP_EOL
            .PHP_EOL
            .'Si el problema continúa, registra una incidencia para que el equipo de TI pueda revisarlo.';

        $redirect = $this->getRedirect(
            key: 'incidencia',
            destination: 'create'
        );

        $quickActions = [
            [
                'label' => 'Sigue sin funcionar',
                'action' => 'send',
                'value' => 'sigue sin funcionar',
            ],

            $this->redirectAction(
                label: 'Reportar incidencia',
                moduleKey: 'incidencia'
            ),

            [
                'label' => 'Consultar mis gestiones',
                'action' => 'status',
                'value' => 'gestion.estado',
            ],

            [
                'label' => 'Volver al menú',
                'action' => 'flow',
                'value' => 'menu.principal',
                'context' => [],
            ],
        ];

        return array_merge(
            $this->baseResponse(
                message: $message,
                quickActions: $quickActions,
                redirect: $redirect,
                mode: 'flow'
            ),
            [
                'diagnostic' => [
                    'key' => $this->nullableText(
                        $diagnostic['key'] ?? null,
                        100
                    ),

                    'score' => is_numeric(
                        $diagnostic['score'] ?? null
                    )
                        ? (float) $diagnostic['score']
                        : 0.0,

                    'matched' => is_array(
                        $diagnostic['matched'] ?? null
                    )
                        ? array_values(
                            $diagnostic['matched']
                        )
                        : [],
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar pasos de diagnóstico
    |--------------------------------------------------------------------------
    |
    | Limpia y limita los pasos sugeridos por el diagnóstico para mostrar
    | únicamente instrucciones breves y utilizables dentro de la respuesta.
    |
    */

    private function prepareDiagnosticSteps(
        mixed $steps
    ): array {
        if (! is_array($steps)) {
            return [];
        }

        $prepared = [];

        foreach ($steps as $step) {
            if (! is_scalar($step)) {
                continue;
            }

            $step = $this->prepareLongText(
                value: $step,
                limit: 350
            );

            if ($step === '') {
                continue;
            }

            $prepared[] = rtrim(
                $step,
                ". \t\n\r\0\x0B"
            ).'.';

            /*
             * Máximo tres pasos para mantener una respuesta breve.
             */
            if (count($prepared) >= 3) {
                break;
            }
        }

        return $prepared;
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta generada por IA
    |--------------------------------------------------------------------------
    |
    | Convierte una AIResponse en la estructura utilizada por el frontend,
    | aplica acciones rápidas seguras y conserva los metadatos relevantes
    | del proveedor de inteligencia artificial.
    |
    */

    private function buildAIResponse(
        ?AIResponse $aiResponse
    ): array {
        if (
            $aiResponse === null
            || ! $aiResponse->hasResponse()
        ) {
            return $this->baseResponse(
                message:
                    'No pude obtener una respuesta en este momento. '
                    .'Puedes intentarlo nuevamente o registrar una incidencia.',
                quickActions: [
                    [
                        'label' => 'Intentar nuevamente',
                        'action' => 'flow',
                        'value' => 'ai.enable',
                        'context' => [],
                    ],

                    $this->redirectAction(
                        label: 'Reportar incidencia',
                        moduleKey: 'incidencia'
                    ),

                    [
                        'label' => 'Volver al menú',
                        'action' => 'flow',
                        'value' => 'menu.principal',
                        'context' => [],
                    ],
                ],
                redirect: $this->getRedirect(
                    key: 'incidencia',
                    destination: 'create'
                ),
                mode: 'ai',
                ai: [
                    'source' => 'fallback',
                    'category' => 'system',
                    'confidence' => 0.0,
                    'truncated' => false,
                    'reused' => false,
                    'metadata' => [],
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Preparar acciones de IA
        |--------------------------------------------------------------------------
        |
        | Las acciones recibidas mediante AIResponse se validan. Las URLs
        | externas proporcionadas directamente por un modelo no se aceptan:
        | las redirecciones se reconstruyen mediante las rutas configuradas.
        |
        */

        $quickActions = $aiResponse->quickActions !== []
            ? $this->normalizeQuickActions(
                $aiResponse->quickActions
            )
            : $this->aiQuickActions(
                $aiResponse
            );

        if ($quickActions === []) {
            $quickActions = $this->aiQuickActions(
                $aiResponse
            );
        }

        $quickActions = $this->removeLegacyMenuActions(
            $quickActions
        );

        return $this->baseResponse(
            message: $aiResponse->message,
            quickActions: $quickActions,
            redirect: $this->getRedirectForAICategory(
                $aiResponse
            ),
            mode: 'ai',
            ai: [
                'source' => $aiResponse->provider()
                    ?? 'unknown',

                'category' => $aiResponse->category,

                'confidence' => $aiResponse->confidence,

                'truncated' => $aiResponse->isTruncated(),

                'reused' => $aiResponse->isReused(),

                'metadata' => $aiResponse->metadata,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones predeterminadas para IA
    |--------------------------------------------------------------------------
    |
    | Determina las acciones sugeridas según la categoría y estado de la
    | respuesta de IA, incluyendo reintentos, incidencias, solicitudes
    | y pases cuando corresponde.
    |
    */

    private function aiQuickActions(
        AIResponse $aiResponse
    ): array {
        $category = $this->normalizeText(
            $aiResponse->category
        );

        /*
        |--------------------------------------------------------------------------
        | Proveedor ocupado o no disponible
        |--------------------------------------------------------------------------
        */

        if (
            $aiResponse->isBusy()
            || $aiResponse->isFallback()
            || $category === 'system'
            || $aiResponse->confidence <= 0
        ) {
            return [
                [
                    'label' => 'Intentar nuevamente',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                    'context' => [],
                ],

                $this->redirectAction(
                    label: 'Reportar incidencia',
                    moduleKey: 'incidencia'
                ),

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                    'context' => [],
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Solicitud
        |--------------------------------------------------------------------------
        */

        if ($this->isRequestCategory($category)) {
            return [
                $this->redirectAction(
                    label: 'Crear solicitud',
                    moduleKey: 'solicitud'
                ),

                [
                    'label' => 'Consultar gestiones',
                    'action' => 'status',
                    'value' => 'gestion.estado',
                ],

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                    'context' => [],
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Pase menor a 24 horas
        |--------------------------------------------------------------------------
        */

        if ($this->isTemporaryPassCategory($category)) {
            return [
                $this->redirectAction(
                    label: 'Crear pase temporal',
                    moduleKey: 'pase_menor_24h'
                ),

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                    'context' => [],
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Autorización mayor a 24 horas
        |--------------------------------------------------------------------------
        */

        if ($this->isAuthorizationCategory($category)) {
            return [
                $this->redirectAction(
                    label: 'Crear pase mayor a 24 horas',
                    moduleKey: 'autorizacion_memorando'
                ),

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                    'context' => [],
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Respuesta técnica general
        |--------------------------------------------------------------------------
        |
        | ChatbotService agregará posteriormente los botones de prellenado y
        | los botones definidos en el nodo ai.enable.
        |
        */

        return [
            [
                'label' => 'Sigue sin funcionar',
                'action' => 'send',
                'value' => 'sigue sin funcionar',
            ],

            $this->redirectAction(
                label: 'Reportar incidencia',
                moduleKey: 'incidencia'
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta para módulo
    |--------------------------------------------------------------------------
    |
    | Construye una respuesta orientada a un módulo específico e incluye
    | accesos para crear la gestión, consultar su historial y volver al menú.
    |
    */

    private function forModule(
        string $key,
        string $message
    ): array {
        $createRedirect = $this->getRedirect(
            key: $key,
            destination: 'create'
        );

        $indexRedirect = $this->getRedirect(
            key: $key,
            destination: 'index'
        );

        $quickActions = [];

        if ($createRedirect !== null) {
            $quickActions[] = [
                'label' => $createRedirect['action_label'],
                'action' => 'redirect',
                'url' => $createRedirect['url'],
            ];
        }

        if ($indexRedirect !== null) {
            $quickActions[] = [
                'label' => $indexRedirect['action_label'],
                'action' => 'redirect',
                'url' => $indexRedirect['url'],
            ];
        }

        $quickActions[] = [
            'label' => 'Volver al menú',
            'action' => 'flow',
            'value' => 'menu.principal',
            'context' => [],
        ];

        return $this->baseResponse(
            message: $message,
            quickActions: $quickActions,
            redirect: $createRedirect,
            mode: 'flow'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Acción de redirección
    |--------------------------------------------------------------------------
    |
    | Construye una acción segura de navegación utilizando únicamente
    | rutas configuradas para los módulos reconocidos por el chatbot.
    |
    */

    private function redirectAction(
        string $label,
        string $moduleKey,
        string $destination = 'create'
    ): array {
        $redirect = $this->getRedirect(
            key: $moduleKey,
            destination: $destination
        );

        if (
            $redirect === null
            || empty($redirect['url'])
        ) {
            return [
                'label' => $label,
                'action' => 'flow',
                'value' => 'menu.principal',
                'context' => [],
            ];
        }

        return [
            'label' => $label,
            'action' => 'redirect',
            'url' => $redirect['url'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener redirección configurada
    |--------------------------------------------------------------------------
    |
    | Valida el módulo y destino solicitados, comprueba la existencia de
    | la ruta en Laravel y construye los datos necesarios para navegar.
    |
    */

    private function getRedirect(
        string $key,
        string $destination = 'create'
    ): ?array {
        if (
            ! in_array(
                $destination,
                ['create', 'index'],
                true
            )
        ) {
            return null;
        }

        if (
            preg_match(
                '/^[a-z0-9_-]+$/',
                $key
            ) !== 1
        ) {
            return null;
        }

        $module = config(
            "chatbot.modules.{$key}"
        );

        if (! is_array($module)) {
            return null;
        }

        $routeName = $module[$destination] ?? null;

        if (
            ! is_string($routeName)
            || trim($routeName) === ''
            || ! Route::has($routeName)
        ) {
            return null;
        }

        $moduleLabel = $this->prepareText(
            value: $module['label'] ?? 'Módulo',
            limit: 100,
            fallback: 'Módulo'
        );

        $actionLabel = $destination === 'index'
            ? $this->getHistoryLabel($key)
            : $this->getCreateLabel($key);

        return [
            'label' => $destination === 'index'
                ? 'Ver: '.$actionLabel
                : 'Ir a: '.$moduleLabel,

            'action_label' => $actionLabel,

            'url' => route(
                $routeName
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Etiquetas para creación
    |--------------------------------------------------------------------------
    |
    | Devuelve la etiqueta utilizada para representar la acción de crear
    | una nueva gestión según el módulo seleccionado.
    |
    */

    private function getCreateLabel(
        string $moduleKey
    ): string {
        return match ($moduleKey) {
            'incidencia' =>
                'Reportar incidencia',

            'solicitud' =>
                'Crear solicitud',

            'pase_menor_24h' =>
                'Crear pase temporal',

            'autorizacion_memorando' =>
                'Crear pase mayor a 24 horas',

            default =>
                'Crear gestión',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Etiquetas para historiales
    |--------------------------------------------------------------------------
    |
    | Devuelve la etiqueta utilizada para consultar el historial del
    | módulo correspondiente dentro del Portal TI.
    |
    */

    private function getHistoryLabel(
        string $moduleKey
    ): string {
        return match ($moduleKey) {
            'incidencia' =>
                'Mis incidencias',

            'solicitud' =>
                'Mis solicitudes',

            'pase_menor_24h',
            'autorizacion_memorando' =>
                'Mis pases',

            default =>
                'Consultar historial',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Redirección sugerida según categoría IA
    |--------------------------------------------------------------------------
    |
    | Determina si la categoría devuelta por la IA corresponde a una
    | gestión concreta y, de ser así, prepara la redirección apropiada.
    |
    */

    private function getRedirectForAICategory(
        AIResponse $aiResponse
    ): ?array {
        if (
            $aiResponse->isBusy()
            || $aiResponse->isFallback()
        ) {
            return null;
        }

        $category = $this->normalizeText(
            $aiResponse->category
        );

        if ($this->isRequestCategory($category)) {
            return $this->getRedirect(
                key: 'solicitud',
                destination: 'create'
            );
        }

        if ($this->isTemporaryPassCategory($category)) {
            return $this->getRedirect(
                key: 'pase_menor_24h',
                destination: 'create'
            );
        }

        if ($this->isAuthorizationCategory($category)) {
            return $this->getRedirect(
                key: 'autorizacion_memorando',
                destination: 'create'
            );
        }

        /*
         * La categoría "ti" es general. No se establece una redirección
         * automática porque ChatbotService agregará los botones adecuados
         * según el mensaje y la intención original.
         */
        if (
            in_array(
                $category,
                [
                    'ti',
                    'general',
                    'concepto',
                    'informacion',
                    'información',
                ],
                true
            )
        ) {
            return null;
        }

        return $this->getRedirect(
            key: 'incidencia',
            destination: 'create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar acciones recibidas
    |--------------------------------------------------------------------------
    |
    | Valida las acciones proporcionadas por AIResponse y reconstruye
    | únicamente aquellas compatibles con los tipos admitidos por el portal.
    |
    */

    private function normalizeQuickActions(
        array $actions
    ): array {
        $normalized = [];

        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            $label = $this->prepareText(
                value: $action['label'] ?? '',
                limit: 120
            );

            if ($label === '') {
                continue;
            }

            $type = $this->normalizeText(
                (string) (
                    $action['action']
                    ?? $action['type']
                    ?? 'send'
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Enviar mensaje
            |--------------------------------------------------------------------------
            */

            if ($type === 'send') {
                $value = $this->prepareText(
                    value:
                        $action['value']
                        ?? $action['message']
                        ?? $label,
                    limit: 500
                );

                if ($value === '') {
                    continue;
                }

                $normalized[] = [
                    'label' => $label,
                    'action' => 'send',
                    'value' => $value,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Acción de flujo
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $type,
                    ['flow', 'ai.enable', 'status'],
                    true
                )
            ) {
                $value = $this->prepareActionIdentifier(
                    $action['value'] ?? ''
                );

                if ($value === '') {
                    continue;
                }

                $normalized[] = [
                    'label' => $label,
                    'action' => $type,
                    'value' => $value,
                    'context' => is_array(
                        $action['context'] ?? null
                    )
                        ? $action['context']
                        : [],
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Redirección
            |--------------------------------------------------------------------------
            |
            | No se acepta una URL libre proveniente de IA. Se requiere una
            | clave de módulo reconocida y Laravel construye la URL real.
            |
            */

            if (
                in_array(
                    $type,
                    ['redirect', 'link', 'url'],
                    true
                )
            ) {
                $moduleKey = $this->prepareModuleKey(
                    $action['module']
                        ?? $action['module_key']
                        ?? ''
                );

                if ($moduleKey === null) {
                    $moduleKey = $this->inferModuleFromLabel(
                        $label
                    );
                }

                if ($moduleKey === null) {
                    continue;
                }

                $destination = $this->normalizeDestination(
                    $action['destination'] ?? 'create'
                );

                $redirect = $this->getRedirect(
                    key: $moduleKey,
                    destination: $destination
                );

                if ($redirect === null) {
                    continue;
                }

                $normalized[] = [
                    'label' => $label,
                    'action' => 'redirect',
                    'url' => $redirect['url'],
                ];
            }
        }

        return $this->uniqueQuickActions(
            $normalized
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar acción antigua "Mostrar menú"
    |--------------------------------------------------------------------------
    |
    | Filtra acciones heredadas que ya no deben mostrarse para evitar
    | duplicar o mezclar comportamientos antiguos con el flujo actual.
    |
    */

    private function removeLegacyMenuActions(
        array $actions
    ): array {
        $filtered = [];

        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            $label = $this->normalizeText(
                (string) (
                    $action['label']
                    ?? ''
                )
            );

            $value = $this->normalizeText(
                (string) (
                    $action['value']
                    ?? ''
                )
            );

            if (
                in_array(
                    $label,
                    [
                        'mostrar menu',
                        'mostrar menú',
                    ],
                    true
                )
                || $value === 'menu'
            ) {
                continue;
            }

            $filtered[] = $action;
        }

        return array_values(
            $filtered
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Inferir módulo desde etiqueta
    |--------------------------------------------------------------------------
    |
    | Intenta identificar el módulo asociado a una acción utilizando
    | palabras presentes en su etiqueta cuando no se proporcionó una clave.
    |
    */

    private function inferModuleFromLabel(
        string $label
    ): ?string {
        $normalizedLabel = $this->normalizeText(
            $label
        );

        if (
            str_contains(
                $normalizedLabel,
                'incidencia'
            )
            || str_contains(
                $normalizedLabel,
                'reportar problema'
            )
        ) {
            return 'incidencia';
        }

        if (
            str_contains(
                $normalizedLabel,
                'solicitud'
            )
        ) {
            return 'solicitud';
        }

        /*
         * El pase mayor debe evaluarse antes que el pase temporal.
         */
        if (
            str_contains(
                $normalizedLabel,
                'pase mayor'
            )
            || str_contains(
                $normalizedLabel,
                'autorizacion'
            )
            || str_contains(
                $normalizedLabel,
                'memorando'
            )
        ) {
            return 'autorizacion_memorando';
        }

        if (
            str_contains(
                $normalizedLabel,
                'pase'
            )
            || str_contains(
                $normalizedLabel,
                'temporal'
            )
        ) {
            return 'pase_menor_24h';
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar clave de módulo
    |--------------------------------------------------------------------------
    |
    | Normaliza aliases y valida que la clave recibida corresponda a uno
    | de los módulos reconocidos por el chatbot.
    |
    */

    private function prepareModuleKey(
        mixed $moduleKey
    ): ?string {
        if (! is_scalar($moduleKey)) {
            return null;
        }

        $moduleKey = $this->normalizeText(
            (string) $moduleKey
        );

        $aliases = [
            'incidencias' => 'incidencia',

            'solicitudes' => 'solicitud',

            'pase' => 'pase_menor_24h',
            'pase_temporal' => 'pase_menor_24h',

            'autorizacion' => 'autorizacion_memorando',
            'memorando' => 'autorizacion_memorando',
        ];

        $moduleKey = $aliases[$moduleKey]
            ?? $moduleKey;

        return in_array(
            $moduleKey,
            [
                'incidencia',
                'solicitud',
                'pase_menor_24h',
                'autorizacion_memorando',
            ],
            true
        )
            ? $moduleKey
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar destino
    |--------------------------------------------------------------------------
    |
    | Limita el destino solicitado a las vistas de creación o historial
    | admitidas por la configuración de módulos.
    |
    */

    private function normalizeDestination(
        mixed $destination
    ): string {
        $destination = $this->normalizeText(
            is_scalar($destination)
                ? (string) $destination
                : ''
        );

        return $destination === 'index'
            ? 'index'
            : 'create';
    }

    /*
    |--------------------------------------------------------------------------
    | Categorías de IA
    |--------------------------------------------------------------------------
    |
    | Proporciona métodos auxiliares para clasificar la categoría devuelta
    | por la IA como solicitud, pase temporal o autorización.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Detectar categoría de solicitud
    |--------------------------------------------------------------------------
    |
    | Comprueba si la categoría proporcionada corresponde a una solicitud
    | o a alguno de los servicios que se gestionan mediante dicho módulo.
    |
    */

    private function isRequestCategory(
        string $category
    ): bool {
        return in_array(
            $category,
            [
                'solicitud',
                'software',
                'programa',
                'instalacion',
                'instalación',
                'acceso',
                'vpn',
                'cuenta',
                'contrasena',
                'contraseña',
                'equipo',
                'impresora',
                'correo',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Detectar categoría de pase temporal
    |--------------------------------------------------------------------------
    |
    | Comprueba si la categoría corresponde a un pase con duración menor
    | a 24 horas.
    |
    */

    private function isTemporaryPassCategory(
        string $category
    ): bool {
        return in_array(
            $category,
            [
                'pase',
                'pase_temporal',
                'pase_menor_24h',
                'pase menor a 24 horas',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Detectar categoría de autorización
    |--------------------------------------------------------------------------
    |
    | Comprueba si la categoría corresponde a una autorización o pase con
    | duración mayor a 24 horas.
    |
    */

    private function isAuthorizationCategory(
        string $category
    ): bool {
        return in_array(
            $category,
            [
                'autorizacion',
                'autorización',
                'memorando',
                'autorizacion_memorando',
                'pase_mayor_24h',
                'pase mayor a 24 horas',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Agregar información de intención
    |--------------------------------------------------------------------------
    |
    | Adjunta los datos de la intención detectada y garantiza que la
    | respuesta final conserve una estructura consistente para el frontend.
    |
    */

    private function appendIntent(
        array $response,
        IntentResult $intent
    ): array {
        $response['intent'] = [
            'name' => $intent->intent,

            'score' => $intent->score,

            'confidence' => $intent->confidence,

            'matched' => is_array(
                $intent->matchedKeywords
            )
                ? $intent->matchedKeywords
                : [],

            'alternatives' => is_array(
                $intent->alternatives
            )
                ? $intent->alternatives
                : [],
        ];

        /*
         * Garantizar la estructura completa aunque una respuesta particular
         * no haya definido todas las claves.
         */
        $response['message'] = trim(
            (string) (
                $response['message']
                ?? ''
            )
        );

        $response['quick_actions'] = is_array(
            $response['quick_actions']
                ?? null
        )
            ? $this->uniqueQuickActions(
                $response['quick_actions']
            )
            : [];

        $response['redirect'] = is_array(
            $response['redirect']
                ?? null
        )
            ? $response['redirect']
            : null;

        $response['items'] = is_array(
            $response['items']
                ?? null
        )
            ? $response['items']
            : [];

        $response['mode'] = is_string(
            $response['mode']
                ?? null
        )
            ? $response['mode']
            : 'flow';

        $response['flow_context'] = is_array(
            $response['flow_context']
                ?? null
        )
            ? $response['flow_context']
            : [];

        $response['ai'] = is_array(
            $response['ai']
                ?? null
        )
            ? $response['ai']
            : null;

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | Acciones rápidas principales
    |--------------------------------------------------------------------------
    |
    | Define las acciones básicas que se muestran cuando el usuario debe
    | elegir entre reportar una incidencia, crear una solicitud o consultar
    | el estado de sus gestiones.
    |
    */

    private function defaultQuickActions(): array
    {
        return [
            [
                'label' => 'Reportar incidencia',
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
                'label' => 'Consultar estado',
                'action' => 'status',
                'value' => 'gestion.estado',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Menú principal con vínculos directos
    |--------------------------------------------------------------------------
    |
    | Construye el menú principal utilizando accesos directos seguros a
    | los módulos configurados y a la consulta de gestiones.
    |
    */

    private function mainMenuActions(): array
    {
        return $this->uniqueQuickActions(
            [
                $this->redirectAction(
                    label: 'Reportar incidencia',
                    moduleKey: 'incidencia',
                    destination: 'create'
                ),

                $this->redirectAction(
                    label: 'Crear solicitud',
                    moduleKey: 'solicitud',
                    destination: 'create'
                ),

                $this->redirectAction(
                    label: 'Pase menor a 24 horas',
                    moduleKey: 'pase_menor_24h',
                    destination: 'create'
                ),

                $this->redirectAction(
                    label: 'Pase mayor a 24 horas',
                    moduleKey: 'autorizacion_memorando',
                    destination: 'create'
                ),

                [
                    'label' => 'Consultar gestiones',
                    'action' => 'status',
                    'value' => 'gestion.estado',
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar acciones duplicadas
    |--------------------------------------------------------------------------
    |
    | Elimina acciones rápidas repetidas comparando su tipo, valor, URL y
    | etiqueta para mantener una respuesta limpia y consistente.
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

            $label = $this->prepareText(
                value: $action['label'] ?? '',
                limit: 120
            );

            if ($label === '') {
                continue;
            }

            $action['label'] = $label;

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

                    $label,
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
    | Preparar identificador de acción
    |--------------------------------------------------------------------------
    |
    | Valida y limita los identificadores internos utilizados por las
    | acciones de flujo, estado y activación de IA.
    |
    */

    private function prepareActionIdentifier(
        mixed $value
    ): string {
        if (! is_scalar($value)) {
            return '';
        }

        $value = strtolower(
            trim((string) $value)
        );

        if (
            preg_match(
                '/^[a-z0-9_.-]+$/',
                $value
            ) !== 1
        ) {
            return '';
        }

        return mb_substr(
            $value,
            0,
            150
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar texto
    |--------------------------------------------------------------------------
    |
    | Limpia etiquetas HTML, caracteres de control y espacios repetidos,
    | además de limitar la longitud de textos cortos utilizados en respuestas.
    |
    */

    private function prepareText(
        mixed $value,
        int $limit,
        string $fallback = ''
    ): string {
        if (! is_scalar($value)) {
            return $fallback;
        }

        $value = trim(
            strip_tags(
                (string) $value
            )
        );

        if ($value === '') {
            return $fallback;
        }

        $value = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            ' ',
            $value
        ) ?? $value;

        $value = preg_replace(
            '/\s+/u',
            ' ',
            $value
        ) ?? $value;

        $value = mb_substr(
            trim($value),
            0,
            max(1, $limit)
        );

        return $value !== ''
            ? $value
            : $fallback;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar texto extenso
    |--------------------------------------------------------------------------
    |
    | Limpia caracteres de control y normaliza saltos de línea conservando
    | la estructura básica de textos largos antes de limitar su longitud.
    |
    */

    private function prepareLongText(
        mixed $value,
        int $limit
    ): string {
        if (! is_scalar($value)) {
            return '';
        }

        $value = trim(
            strip_tags(
                (string) $value
            )
        );

        if ($value === '') {
            return '';
        }

        $value = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $value
        ) ?? $value;

        $value = preg_replace(
            "/[ \t]+\n/u",
            "\n",
            $value
        ) ?? $value;

        $value = preg_replace(
            "/\n{3,}/u",
            PHP_EOL.PHP_EOL,
            $value
        ) ?? $value;

        return mb_substr(
            trim($value),
            0,
            max(1, $limit)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar texto opcional
    |--------------------------------------------------------------------------
    |
    | Normaliza un valor de texto y devuelve null cuando el resultado no
    | contiene información utilizable.
    |
    */

    private function nullableText(
        mixed $value,
        int $limit
    ): ?string {
        $value = $this->prepareText(
            value: $value,
            limit: $limit
        );

        return $value !== ''
            ? $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar texto
    |--------------------------------------------------------------------------
    |
    | Convierte el texto a minúsculas, elimina variaciones de caracteres
    | acentuados y normaliza espacios para facilitar comparaciones internas.
    |
    */

    private function normalizeText(
        string $value
    ): string {
        $value = mb_strtolower(
            trim($value),
            'UTF-8'
        );

        $value = strtr(
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

        return preg_replace(
            '/\s+/u',
            ' ',
            $value
        ) ?? $value;
    }
}