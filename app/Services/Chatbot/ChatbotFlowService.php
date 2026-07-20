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
        string $userName = 'usuario'
    ): ?array {
        $action = trim($action);

        if (
            $action === ''
            || !$this->isValidAction($action)
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

        if (!is_array($node)) {
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

        return [
            'message' =>
                $message !== ''
                    ? $message
                    : 'Selecciona una opción para continuar.',

            'quick_actions' =>
                $this->prepareActions(
                    $node['quick_actions']
                    ?? []
                ),

            'redirect' => null,

            'items' => [],

            'mode' =>
                $node['mode']
                ?? 'flow',

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
            $userName
        ) ?? [
            'message' =>
                '¿En qué puedo ayudarte?',

            'quick_actions' => [],

            'redirect' => null,

            'items' => [],

            'mode' => 'flow',

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
        if (!$this->isValidAction($action)) {
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
        mixed $actions
    ): array {
        if (!is_array($actions)) {
            return [];
        }

        $prepared = [];

        foreach ($actions as $action) {
            if (!is_array($action)) {
                continue;
            }

            $label = trim(
                (string) (
                    $action['label']
                    ?? ''
                )
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
             * Convertir módulos del portal en URLs reales.
             */
            if ($type === 'redirect') {
                $redirect =
                    $this->prepareRedirectAction(
                        $action,
                        $label
                    );

                if ($redirect !== null) {
                    $prepared[] = $redirect;
                }

                continue;
            }

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
];
        }

        return $prepared;
    }

    /*
    |--------------------------------------------------------------------------
    | Construir redirección
    |--------------------------------------------------------------------------
    */

    private function prepareRedirectAction(
        array $action,
        string $label
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
            !is_string($routeName)
            || $routeName === ''
            || !Route::has($routeName)
        ) {
            return null;
        }

        return [
    'label' => $label,

    'icon' => $this->prepareIcon(
        $action['icon']
        ?? null
    ),

    'action' => 'redirect',

    'url' => route($routeName),
];
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

    /*
     * Los nombres de Lucide utilizan letras,
     * números y guiones.
     */
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