<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Route;

class ChatbotFlowService
{
    /*
    |--------------------------------------------------------------------------
    | Procesar acción interactiva
    |--------------------------------------------------------------------------
    */

    public function handle(
        string $action,
        string $userName = 'usuario',
        array $context = []
    ): ?array {
        $action = trim($action);

        if (
            $action === ''
            || ! $this->isValidAction($action)
        ) {
            return null;
        }

        $nodes = config(
            'chatbot_flows.nodes',
            []
        );

        if (! is_array($nodes)) {
            return null;
        }

        $node = $nodes[$action] ?? null;

        if (! is_array($node)) {
            return null;
        }

        $userName = $this->prepareUserName(
            $userName
        );

        $message = $this->prepareMessage(
            $node['message'] ?? ''
        );

        $message = str_replace(
            '{usuario}',
            $userName,
            $message
        );

        /*
        |--------------------------------------------------------------------------
        | Contexto acumulado
        |--------------------------------------------------------------------------
        |
        | Primero se prepara el contexto recibido desde el paso anterior.
        | Después se agregan los datos definidos por el nodo actual.
        | Los datos del nodo tienen prioridad.
        |
        */

        $currentContext = $this->preparePrefill(
            $context
        );

        $nodePrefill = $this->preparePrefill(
            $node['prefill'] ?? []
        );

        $currentContext = array_replace(
            $currentContext,
            $nodePrefill
        );

        $mode = $this->prepareMode(
            $node['mode'] ?? 'flow'
        );

        return [
            'message' => $message !== ''
                ? $message
                : 'Selecciona una opción para continuar.',

            'quick_actions' => $this->prepareActions(
                actions: $node['quick_actions'] ?? [],
                context: $currentContext,
                nodeMessage: $message,
                userName: $userName
            ),

            'redirect' => null,

            'items' => [],

            'mode' => $mode,

            /*
             * El frontend debe devolver este contexto en la siguiente
             * acción para conservar los datos recopilados.
             */
            'flow_context' => $currentContext,

            'intent' => [
                'name' => 'flow',
                'score' => 1.0,
                'confidence' => 1.0,
                'action' => $action,
            ],

            'ai' => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener menú inicial
    |--------------------------------------------------------------------------
    */

    public function menu(
        string $userName = 'usuario'
    ): array {
        $start = trim(
            (string) config(
                'chatbot_flows.start',
                'menu.principal'
            )
        );

        if (
            $start === ''
            || ! $this->isValidAction($start)
        ) {
            $start = 'menu.principal';
        }

        return $this->handle(
            action: $start,
            userName: $userName,
            context: []
        ) ?? $this->defaultMenuResponse();
    }

    /*
    |--------------------------------------------------------------------------
    | Comprobar si existe una acción
    |--------------------------------------------------------------------------
    */

    public function exists(
        string $action
    ): bool {
        $action = trim($action);

        if (! $this->isValidAction($action)) {
            return false;
        }

        $nodes = config(
            'chatbot_flows.nodes',
            []
        );

        return is_array($nodes)
            && isset($nodes[$action])
            && is_array($nodes[$action]);
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar botones
    |--------------------------------------------------------------------------
    */

    private function prepareActions(
        mixed $actions,
        array $context = [],
        string $nodeMessage = '',
        string $userName = 'usuario'
    ): array {
        if (! is_array($actions)) {
            return [];
        }

        $prepared = [];

        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            $label = $this->prepareLabel(
                $action['label'] ?? ''
            );

            if ($label === '') {
                continue;
            }

            $description = $this->prepareDescription(
                $action['description'] ?? null
            );

            $type = $this->prepareActionType(
                $action['action'] ?? 'flow'
            );

            if ($type === null) {
                continue;
            }

            /*
             * Los datos definidos por el botón tienen prioridad sobre
             * los datos acumulados durante el recorrido.
             */
            $actionPrefill = $this->preparePrefill(
                $action['prefill'] ?? []
            );

            $actionContext = array_replace(
                $context,
                $actionPrefill
            );

            /*
            |--------------------------------------------------------------------------
            | Redirección a formulario
            |--------------------------------------------------------------------------
            */

            if ($type === 'redirect') {
                $redirect = $this->prepareRedirectAction(
                    action: $action,
                    label: $label,
                    description: $description,
                    context: $actionContext
                );

                if ($redirect !== null) {
                    $prepared[] = $redirect;
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Contactar a Helpdesk
            |--------------------------------------------------------------------------
            */

            if ($type === 'helpdesk') {
                $helpdesk = $this->prepareHelpdeskAction(
                    action: $action,
                    label: $label,
                    description: $description,
                    context: $actionContext,
                    nodeMessage: $nodeMessage,
                    userName: $userName
                );

                if ($helpdesk !== null) {
                    $prepared[] = $helpdesk;
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Acciones normales
            |--------------------------------------------------------------------------
            */

            $value = trim(
                (string) ($action['value'] ?? '')
            );

            if (
                $value === ''
                || ! $this->isValidActionValue($value, $type)
            ) {
                continue;
            }

            $preparedAction = [
                'label' => $label,

                'description' => $description,

                'icon' => $this->prepareIcon(
                    $action['icon'] ?? null
                ),

                'variant' => $this->prepareVariant(
                    $action['variant'] ?? null
                ),

                'action' => $type,

                'value' => mb_substr(
                    $value,
                    0,
                    200
                ),

                /*
                 * El frontend enviará este contexto junto con
                 * la siguiente acción.
                 */
                'context' => $actionContext,
            ];

            /*
             * Permite que una acción normal indique explícitamente
             * que debe habilitar la entrada libre de IA.
             */
            if ($type === 'ai.enable') {
                $preparedAction['mode'] = 'ai';
            }

            $prepared[] = $preparedAction;
        }

        return $prepared;
    }

    /*
    |--------------------------------------------------------------------------
    | Construir redirección con prellenado
    |--------------------------------------------------------------------------
    */

    private function prepareRedirectAction(
        array $action,
        string $label,
        ?string $description = null,
        array $context = []
    ): ?array {
        $module = trim(
            (string) ($action['module'] ?? '')
        );

        if (
            $module === ''
            || preg_match('/^[a-z0-9_-]+$/', $module) !== 1
        ) {
            return null;
        }

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

        $prefill = $this->filterPrefillByModule(
            module: $module,
            prefill: $context
        );

        /*
         * Los datos se agregan como parámetros de consulta mediante
         * route(). Los formularios podrán leerlos desde request().
         */
        $url = empty($prefill)
            ? route($routeName)
            : route($routeName, $prefill);

        return [
            'label' => $label,

            'description' => $description,

            'icon' => $this->prepareIcon(
                $action['icon'] ?? null
            ),

            'variant' => $this->prepareVariant(
                $action['variant'] ?? null
            ),

            'action' => 'redirect',

            'url' => $url,

            'module' => $module,

            'context' => $context,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Construir botón de Helpdesk
    |--------------------------------------------------------------------------
    */

    private function prepareHelpdeskAction(
        array $action,
        string $label,
        ?string $description = null,
        array $context = [],
        string $nodeMessage = '',
        string $userName = 'usuario'
    ): ?array {
        $helpdeskEmail = trim(
            (string) config(
                'chatbot.helpdesk_email',
                'helpdesk@televicentro.hn'
            )
        );

        if (
            $helpdeskEmail === ''
            || filter_var(
                $helpdeskEmail,
                FILTER_VALIDATE_EMAIL
            ) === false
        ) {
            return null;
        }

        $customSubject = $this->prepareEmailSubject(
            $action['subject'] ?? ''
        );

        $title = $this->prepareEmailSubject(
            $context['titulo']
                ?? $context['asunto']
                ?? 'Problema reportado desde el Asistente TI'
        );

        if ($title === '') {
            $title = 'Problema reportado desde el Asistente TI';
        }

        $subject = $customSubject !== ''
            ? $customSubject
            : '[Portal TI] Solicitud de ayuda - '.$title;

        $body = $this->buildHelpdeskBody(
            data: $context,
            nodeMessage: $nodeMessage,
            userName: $userName,
            customBody: $action['body'] ?? null
        );

        $outlookUrl =
            'https://outlook.office.com/mail/deeplink/compose'
            .'?to='.rawurlencode($helpdeskEmail)
            .'&subject='.rawurlencode($subject)
            .'&body='.rawurlencode($body);

        $mailtoUrl =
            'mailto:'.$helpdeskEmail
            .'?subject='.rawurlencode($subject)
            .'&body='.rawurlencode($body);

        return [
            'label' => $label,

            'description' => $description,

            'icon' => $this->prepareIcon(
                $action['icon'] ?? 'headset'
            ),

            'variant' => 'urgent',

            'action' => 'helpdesk',

            'url' => $outlookUrl,

            'fallback_url' => $mailtoUrl,

            /*
             * El frontend debe abrirlo en otra pestaña.
             */
            'target' => '_blank',

            'context' => $context,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Construir contenido del correo a Helpdesk
    |--------------------------------------------------------------------------
    */

    private function buildHelpdeskBody(
        array $data,
        string $nodeMessage,
        string $userName,
        mixed $customBody = null
    ): string {
        $userName = $this->prepareUserName(
            $userName
        );

        $customBody = $this->prepareLongText(
            $customBody,
            4000
        );

        if ($customBody !== '') {
            $customBody = str_replace(
                [
                    '{usuario}',
                    '{correo}',
                    '{diagnostico}',
                    '{titulo}',
                    '{asunto}',
                    '{descripcion}',
                    '{equipo}',
                    '{ubicacion}',
                    '{afectacion}',
                ],
                [
                    $userName,
                    $this->prepareContextValue(
                        $data['correo'] ?? 'N/A'
                    ),
                    $this->prepareLongText(
                        $nodeMessage,
                        1500
                    ),
                    $this->prepareContextValue(
                        $data['titulo'] ?? 'N/A'
                    ),
                    $this->prepareContextValue(
                        $data['asunto'] ?? 'N/A'
                    ),
                    $this->prepareContextValue(
                        $data['descripcion'] ?? 'N/A',
                        2000
                    ),
                    $this->prepareContextValue(
                        $data['equipo'] ?? 'N/A'
                    ),
                    $this->prepareContextValue(
                        $data['ubicacion'] ?? 'N/A'
                    ),
                    $this->prepareContextValue(
                        $data['afectacion'] ?? 'N/A'
                    ),
                ],
                $customBody
            );

            return trim($customBody);
        }

        $lines = [
            'Hola, equipo de soporte TI:',
            '',
            'Necesito apoyo con un problema que no pudo resolverse desde el Asistente TI.',
            '',
            'Usuario: '.$userName,
        ];

        $email = $this->prepareContextValue(
            $data['correo'] ?? ''
        );

        if ($email !== '') {
            $lines[] = 'Correo: '.$email;
        }

        $this->appendContextLine(
            $lines,
            'Problema',
            $data['titulo'] ?? null,
            300
        );

        $this->appendContextLine(
            $lines,
            'Asunto',
            $data['asunto'] ?? null,
            300
        );

        $this->appendContextLine(
            $lines,
            'Descripción',
            $data['descripcion'] ?? null,
            2000
        );

        $this->appendContextLine(
            $lines,
            'Equipo o servicio',
            $data['equipo'] ?? null,
            300
        );

        $this->appendContextLine(
            $lines,
            'Ubicación',
            $data['ubicacion'] ?? null,
            300
        );

        $this->appendContextLine(
            $lines,
            'Afectación',
            $data['afectacion'] ?? null,
            200
        );

        $this->appendContextLine(
            $lines,
            'Categoría',
            $data['categoria'] ?? null,
            300
        );

        $this->appendContextLine(
            $lines,
            'Sistema',
            $data['sistema'] ?? null,
            300
        );

        $this->appendContextLine(
            $lines,
            'Programa',
            $data['programa'] ?? null,
            300
        );

        $nodeMessage = $this->prepareLongText(
            $nodeMessage,
            1500
        );

        if ($nodeMessage !== '') {
            $lines[] = '';
            $lines[] = 'Última orientación mostrada por el asistente:';
            $lines[] = $nodeMessage;
        }

        $lines[] = '';
        $lines[] = 'Por favor, ayúdenme a revisar el caso y dar seguimiento a la gestión correspondiente.';

        return implode(
            PHP_EOL,
            $lines
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Agregar campo al correo
    |--------------------------------------------------------------------------
    */

    private function appendContextLine(
        array &$lines,
        string $label,
        mixed $value,
        int $limit = 500
    ): void {
        $value = $this->prepareContextValue(
            $value,
            $limit
        );

        if ($value === '') {
            return;
        }

        $lines[] = $label.': '.$value;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar datos de prellenado
    |--------------------------------------------------------------------------
    */

    private function preparePrefill(
        mixed $prefill
    ): array {
        if (! is_array($prefill)) {
            return [];
        }

        $allowedKeys = [
            /*
             * Incidencias.
             */
            'titulo',
            'descripcion',
            'tiempo_problema',
            'afectacion',
            'equipo',
            'ubicacion',

            /*
             * Solicitudes.
             */
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
             * Información auxiliar para conversación y Helpdesk.
             */
            'prefill_source',
            'correo',
            'tipo_gestion',
            'management_type',
        ];

        $prepared = [];

        foreach ($prefill as $key => $value) {
            $key = trim(
                (string) $key
            );

            if (
                ! in_array(
                    $key,
                    $allowedKeys,
                    true
                )
            ) {
                continue;
            }

            if (! is_scalar($value)) {
                continue;
            }

            $limit = match ($key) {
                'prefill_source' => 3000,
                'descripcion' => 2000,
                'justificacion',
                'motivo_cambio' => 1000,
                default => 500,
            };

            $value = $this->prepareContextValue(
                $value,
                $limit
            );

            if ($value === '') {
                continue;
            }

            $prepared[$key] = $value;
        }

        return $prepared;
    }

    /*
    |--------------------------------------------------------------------------
    | Filtrar campos según módulo
    |--------------------------------------------------------------------------
    */

    private function filterPrefillByModule(
        string $module,
        array $prefill
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
            $prefill,
            array_flip($allowedFields)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar modo del nodo
    |--------------------------------------------------------------------------
    */

    private function prepareMode(
        mixed $mode
    ): string {
        $mode = strtolower(
            trim((string) $mode)
        );

        return in_array(
            $mode,
            [
                'flow',
                'ai',
                'menu',
                'result',
            ],
            true
        )
            ? $mode
            : 'flow';
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar tipo de acción
    |--------------------------------------------------------------------------
    */

    private function prepareActionType(
        mixed $type
    ): ?string {
        $type = strtolower(
            trim((string) $type)
        );

        return in_array(
            $type,
            [
                'flow',
                'send',
                'status',
                'ai.enable',
                'redirect',
                'helpdesk',
            ],
            true
        )
            ? $type
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Validar valor según la acción
    |--------------------------------------------------------------------------
    */

    private function isValidActionValue(
        string $value,
        string $type
    ): bool {
        /*
         * Las acciones internas utilizan identificadores controlados,
         * por ejemplo: menu.principal, ai.enable o gestion.estado.
         */
        if (
            $type === 'flow'
            || $type === 'status'
            || $type === 'ai.enable'
        ) {
            return $this->isValidAction($value);
        }

        /*
         * Las acciones send pueden contener una frase que se enviará
         * como mensaje del usuario.
         */
        return $type === 'send'
            && mb_strlen($value) <= 500;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar textos
    |--------------------------------------------------------------------------
    */

    private function prepareUserName(
        mixed $userName
    ): string {
        $userName = $this->prepareContextValue(
            $userName,
            150
        );

        return $userName !== ''
            ? $userName
            : 'usuario';
    }

    private function prepareMessage(
        mixed $message
    ): string {
        return $this->prepareLongText(
            $message,
            2000
        );
    }

    private function prepareLabel(
        mixed $label
    ): string {
        return $this->prepareContextValue(
            $label,
            120
        );
    }

    private function prepareDescription(
        mixed $description
    ): ?string {
        $description = $this->prepareContextValue(
            $description,
            300
        );

        return $description !== ''
            ? $description
            : null;
    }

    private function prepareEmailSubject(
        mixed $subject
    ): string {
        $subject = $this->prepareContextValue(
            $subject,
            180
        );

        return str_replace(
            [
                "\r",
                "\n",
            ],
            ' ',
            $subject
        );
    }

    private function prepareContextValue(
        mixed $value,
        int $limit = 500
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
            '/[ \t]+/u',
            ' ',
            $value
        ) ?? $value;

        return mb_substr(
            trim($value),
            0,
            max(1, $limit)
        );
    }

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
    | Preparar icono
    |--------------------------------------------------------------------------
    */

    private function prepareIcon(
        mixed $icon
    ): ?string {
        $icon = strtolower(
            trim((string) $icon)
        );

        if ($icon === '') {
            return null;
        }

        if (
            preg_match(
                '/^[a-z0-9-]+$/',
                $icon
            ) !== 1
        ) {
            return null;
        }

        return mb_substr(
            $icon,
            0,
            80
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar variante visual
    |--------------------------------------------------------------------------
    */

    private function prepareVariant(
        mixed $variant
    ): string {
        $variant = strtolower(
            trim((string) $variant)
        );

        return in_array(
            $variant,
            [
                'default',
                'ai',
                'urgent',
            ],
            true
        )
            ? $variant
            : 'default';
    }

    /*
    |--------------------------------------------------------------------------
    | Validar identificador de nodo
    |--------------------------------------------------------------------------
    */

    private function isValidAction(
        string $action
    ): bool {
        return preg_match(
            '/^[a-z0-9_.-]+$/',
            $action
        ) === 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Respuesta predeterminada
    |--------------------------------------------------------------------------
    */

    private function defaultMenuResponse(): array
    {
        return [
            'message' => '¿En qué puedo ayudarte?',

            'quick_actions' => [],

            'redirect' => null,

            'items' => [],

            'mode' => 'flow',

            'flow_context' => [],

            'intent' => [
                'name' => 'menu',
                'score' => 1.0,
                'confidence' => 1.0,
                'action' => null,
            ],

            'ai' => null,
        ];
    }
}