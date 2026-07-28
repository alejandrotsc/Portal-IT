<?php

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

        $node = is_array($nodes)
            ? ($nodes[$action] ?? null)
            : null;

        if (! is_array($node)) {
            return null;
        }

        $message = trim(
            (string) (
                $node['message']
                ?? ''
            )
        );

        $message = str_replace(
            '{usuario}',
            $userName,
            $message
        );

        /*
         * Contexto recibido desde el recorrido anterior.
         */
        $currentContext = $this->preparePrefill(
            $context
        );

        /*
         * Datos definidos en el nodo actual.
         * El nodo actual tiene prioridad.
         */
        $nodePrefill = $this->preparePrefill(
            $node['prefill'] ?? []
        );

        $currentContext = array_merge(
            $currentContext,
            $nodePrefill
        );

        return [
            'message' =>
                $message !== ''
                    ? $message
                    : 'Selecciona una opción para continuar.',

            'quick_actions' =>
                $this->prepareActions(
                    actions: $node['quick_actions'] ?? [],
                    context: $currentContext,
                    nodeMessage: $message,
                    userName: $userName
                ),

            'redirect' => null,

            'items' => [],

            'mode' =>
                $node['mode']
                ?? 'flow',

            /*
             * Se devuelve para que el frontend lo conserve
             * durante todo el recorrido, incluso en ai.enable.
             */
            'flow_context' => $currentContext,

            'intent' => [
                'name' => 'flow',
                'score' => 1,
                'confidence' => 1,
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
        $start = (string) config(
            'chatbot_flows.start',
            'menu.principal'
        );

        return $this->handle(
            $start,
            $userName,
            []
        ) ?? [
            'message' =>
                '¿En qué puedo ayudarte?',

            'quick_actions' => [],

            'redirect' => null,

            'items' => [],

            'mode' => 'flow',

            'flow_context' => [],

            'intent' => [
                'name' => 'menu',
                'score' => 1,
                'confidence' => 1,
            ],

            'ai' => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Comprobar si existe una acción
    |--------------------------------------------------------------------------
    */

    public function exists(
        string $action
    ): bool {
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

            $label = trim(
                (string) (
                    $action['label']
                    ?? ''
                )
            );

            $description = $this->prepareDescription(
                $action['description']
                ?? null
            );

            $type = trim(
                (string) (
                    $action['action']
                    ?? 'flow'
                )
            );

            if ($label === '') {
                continue;
            }

            /*
             * Datos específicos del botón.
             * Tienen prioridad sobre el contexto acumulado.
             */
            $actionPrefill = $this->preparePrefill(
                $action['prefill'] ?? []
            );

            $actionContext = array_merge(
                $context,
                $actionPrefill
            );

            /*
             * Redirección a formularios del portal.
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
             * Contactar directamente a Helpdesk.
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
             * Acciones normales del flujo.
             */
            $value = trim(
                (string) (
                    $action['value']
                    ?? ''
                )
            );

            if ($value === '') {
                continue;
            }

            $prepared[] = [
                'label' => $label,

                'description' => $description,

                'icon' => $this->prepareIcon(
                    $action['icon']
                    ?? null
                ),

                'variant' => $this->prepareVariant(
                    $action['variant']
                    ?? null
                ),

                'action' => $type,

                'value' => $value,

                /*
                 * El frontend enviará este contexto junto
                 * con la siguiente acción.
                 */
                'context' => $actionContext,
            ];
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
            (string) (
                $action['module']
                ?? ''
            )
        );

        if ($module === '') {
            return null;
        }

        $routeName = config(
            "chatbot.modules.{$module}.create"
        );

        if (
            ! is_string($routeName)
            || $routeName === ''
            || ! Route::has($routeName)
        ) {
            return null;
        }

        $prefill = $this->filterPrefillByModule(
            $module,
            $context
        );

        $url = empty($prefill)
            ? route($routeName)
            : route($routeName, $prefill);

        return [
            'label' => $label,

            'description' => $description,

            'icon' => $this->prepareIcon(
                $action['icon']
                ?? null
            ),

            'variant' => $this->prepareVariant(
                $action['variant']
                ?? null
            ),

            'action' => 'redirect',

            'url' => $url,

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

        $customSubject = trim(
            (string) (
                $action['subject']
                ?? ''
            )
        );

        $title = trim(
            (string) (
                $context['titulo']
                ?? $context['asunto']
                ?? 'Problema reportado desde el Asistente TI'
            )
        );

        $subject = $customSubject !== ''
            ? $customSubject
            : '[Portal TI] Solicitud de ayuda urgente - '.$title;

        $body = $this->buildHelpdeskBody(
            data: $context,
            nodeMessage: $nodeMessage,
            userName: $userName,
            customBody: $action['body'] ?? null
        );

        /*
         * Outlook Web abre directamente el compositor
         * en una pestaña nueva.
         */
        $outlookUrl =
            'https://outlook.office.com/mail/deeplink/compose'
            .'?to='.rawurlencode($helpdeskEmail)
            .'&subject='.rawurlencode($subject)
            .'&body='.rawurlencode($body);

        /*
         * Respaldo para equipos que utilicen otro
         * cliente de correo predeterminado.
         */
        $mailtoUrl =
            'mailto:'.$helpdeskEmail
            .'?subject='.rawurlencode($subject)
            .'&body='.rawurlencode($body);

        return [
            'label' => $label,

            'description' => $description,

            'icon' => $this->prepareIcon(
                $action['icon']
                ?? 'headset'
            ),

            'variant' => 'urgent',

            'action' => 'helpdesk',

            'url' => $outlookUrl,

            'fallback_url' => $mailtoUrl,

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
        $customBody = trim(
            (string) $customBody
        );

        if ($customBody !== '') {
            return str_replace(
                [
                    '{usuario}',
                    '{diagnostico}',
                ],
                [
                    $userName,
                    $nodeMessage,
                ],
                $customBody
            );
        }

        $lines = [
            'Hola, equipo de Helpdesk:',
            '',
            'Necesito apoyo con un problema que no pudo resolverse desde el Asistente TI.',
            '',
            'Usuario: '.$userName,
        ];

        if (! empty($data['titulo'])) {
            $lines[] =
                'Problema: '.$data['titulo'];
        }

        if (! empty($data['asunto'])) {
            $lines[] =
                'Asunto: '.$data['asunto'];
        }

        if (! empty($data['descripcion'])) {
            $lines[] =
                'Descripción: '.$data['descripcion'];
        }

        if (! empty($data['equipo'])) {
            $lines[] =
                'Equipo o servicio: '.$data['equipo'];
        }

        if (! empty($data['ubicacion'])) {
            $lines[] =
                'Ubicación: '.$data['ubicacion'];
        }

        if (! empty($data['afectacion'])) {
            $lines[] =
                'Afectación: '.$data['afectacion'];
        }

        if ($nodeMessage !== '') {
            $lines[] = '';
            $lines[] =
                'Última orientación mostrada por el asistente:';

            $lines[] = $nodeMessage;
        }

        $lines[] = '';
        $lines[] =
            'Por favor, ayúdenme a revisar el caso y elaborar el ticket correspondiente.';

        return implode(
            PHP_EOL,
            $lines
        );
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

        foreach ($prefill as $key => $value) {
            $key = trim(
                (string) $key
            );

            if (! in_array(
                $key,
                $allowedKeys,
                true
            )) {
                continue;
            }

            if (
                ! is_string($value)
                && ! is_numeric($value)
                && ! is_bool($value)
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
    | Filtrar campos según el módulo
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

        if (empty($allowedFields)) {
            return [];
        }

        return array_intersect_key(
            $prefill,
            array_flip($allowedFields)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar descripción del botón
    |--------------------------------------------------------------------------
    */

    private function prepareDescription(
        mixed $description
    ): ?string {
        $description = trim(
            (string) $description
        );

        if ($description === '') {
            return null;
        }

        return mb_substr(
            $description,
            0,
            300
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar nombre de icono
    |--------------------------------------------------------------------------
    */

    private function prepareIcon(
        mixed $icon
    ): ?string {
        $icon = trim(
            (string) $icon
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

        return $icon;
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar variante visual
    |--------------------------------------------------------------------------
    */

    private function prepareVariant(
        mixed $variant
    ): string {
        $variant = trim(
            (string) $variant
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
    | Validar identificador
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
}