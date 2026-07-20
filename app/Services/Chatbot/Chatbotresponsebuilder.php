<?php

namespace App\Services\Chatbot;

use App\Services\Chatbot\AI\AIResponse;
use App\Services\Chatbot\Diagnostics\DiagnosticEngine;
use Illuminate\Support\Facades\Route;

class ChatbotResponseBuilder
{
    public function __construct(
        private readonly DiagnosticEngine $diagnosticEngine
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Construir respuesta principal
    |--------------------------------------------------------------------------
    */

    public function build(
        IntentResult $intent,
        string $userName,
        string $message = '',
        ?AIResponse $aiResponse = null
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Diagnóstico basado en reglas
        |--------------------------------------------------------------------------
        */

        if ($intent->is('incidencia')) {
            $diagnostic = $this->diagnosticEngine->diagnose(
                $message
            );

            if ($diagnostic) {
                return $this->appendIntent(
                    $this->buildDiagnosticResponse(
                        $diagnostic,
                        $userName
                    ),
                    $intent
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
                'incidencia',
                "Entiendo, {$userName}. Puedes registrar un reporte de incidencia para que el equipo de TI revise el problema."
            ),

            'solicitud' => $this->forModule(
                'solicitud',
                "Perfecto, {$userName}. Puedes crear una solicitud para instalaciones, accesos, equipos, cuentas u otros servicios tecnológicos."
            ),

            'pase_menor_24h' => $this->forModule(
                'pase_menor_24h',
                'Para accesos menores a 24 horas debes gestionar un pase temporal.'
            ),

            'autorizacion_memorando' => $this->forModule(
                'autorizacion_memorando',
                'Para accesos mayores a 24 horas debes gestionar una autorización mediante memorando.'
            ),

            'saludo' => [
                'message' => "Hola, {$userName}. ¿En qué puedo ayudarte?",
                'quick_actions' => $this->defaultQuickActions(),
                'redirect' => null,
                'items' => null,
            ],

            'cierre' => [
                'message' => "Excelente, {$userName}. Me alegra saber que el problema quedó resuelto.",
                'quick_actions' => $this->defaultQuickActions(),
                'redirect' => null,
                'items' => null,
            ],

            'menu' => [
                'message' => 'Estas son las opciones disponibles:',
                'quick_actions' => $this->mainMenuActions(),
                'redirect' => null,
                'items' => null,
            ],

            'ai' => $this->buildAIResponse(
                $aiResponse
            ),

            default => [
                'message' =>
                    "No estoy seguro de haber entendido tu solicitud, {$userName}. Selecciona una opción:",

                'quick_actions' => $this->defaultQuickActions(),
                'redirect' => null,
                'items' => null,
            ],
        };

        return $this->appendIntent(
            $response,
            $intent
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Respuesta de diagnóstico
    |--------------------------------------------------------------------------
    */

    private function buildDiagnosticResponse(
        array $diagnostic,
        string $userName
    ): array {
        $steps = array_values(
            array_filter(
                $diagnostic['steps'] ?? [],
                static fn (mixed $step): bool =>
                    is_string($step)
                    && trim($step) !== ''
            )
        );

        $stepsText = '';

        if ($steps !== []) {
            $stepsText =
                "\n\nPuedes probar:\n• "
                .implode(
                    "\n• ",
                    $steps
                );
        }

        $redirect = $this->getRedirect(
            'incidencia'
        );

        return [
            'message' =>
                "{$userName}, "
                .trim(
                    (string) (
                        $diagnostic['message']
                        ?? 'vamos a revisar el problema.'
                    )
                )
                .$stepsText,

            'quick_actions' => [
                [
                    'label' => 'Sigue sin funcionar',
                    'action' => 'send',
                    'value' => 'sigue sin funcionar',
                ],

                $this->redirectAction(
                    'Crear incidencia',
                    'incidencia'
                ),

                [
                    'label' => 'Consultar mis gestiones',
                    'action' => 'send',
                    'value' => 'consultar estado',
                ],

                [
                    'label' => 'Mostrar menú',
                    'action' => 'send',
                    'value' => 'menu',
                ],
            ],

            /*
             * Se mantiene por compatibilidad con el Blade.
             */
            'redirect' => $redirect,

            'items' => null,

            'diagnostic' => [
                'key' => $diagnostic['key'] ?? null,
                'score' => $diagnostic['score'] ?? 0,
                'matched' => $diagnostic['matched'] ?? [],
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Respuesta generada por IA
    |--------------------------------------------------------------------------
    */

    private function buildAIResponse(
        ?AIResponse $aiResponse
    ): array {
        if (!$aiResponse || !$aiResponse->hasResponse()) {
            return [
                'message' =>
                    'No pude obtener una respuesta en este momento. Puedes intentar nuevamente o registrar una incidencia.',

                'quick_actions' => [
                    [
                        'label' => 'Intentar nuevamente',
                        'action' => 'send',
                        'value' => 'necesito ayuda con un problema de TI',
                    ],

                    $this->redirectAction(
                        'Crear incidencia',
                        'incidencia'
                    ),

                    [
                        'label' => 'Mostrar menú',
                        'action' => 'send',
                        'value' => 'menu',
                    ],
                ],

                'redirect' => $this->getRedirect(
                    'incidencia'
                ),

                'items' => null,

                'ai' => [
                    'category' => 'system',
                    'confidence' => 0,
                    'metadata' => [],
                ],
            ];
        }

        /*
         * Si la IA devuelve acciones, se validan y normalizan.
         * Si no devuelve acciones, se generan desde Laravel.
         */
        $quickActions = !empty($aiResponse->quickActions)
            ? $this->normalizeQuickActions(
                $aiResponse->quickActions
            )
            : $this->aiQuickActions(
                $aiResponse
            );

        /*
         * Garantiza que siempre exista al menos una acción útil.
         */
        if ($quickActions === []) {
            $quickActions = $this->aiQuickActions(
                $aiResponse
            );
        }

        return [
            'message' => trim(
                $aiResponse->message
            ),

            'quick_actions' => $quickActions,

            'redirect' => $this->getRedirectForAICategory(
                $aiResponse
            ),

            'items' => null,

            'ai' => [
                'category' => $aiResponse->category,
                'confidence' => $aiResponse->confidence,
                'metadata' => $aiResponse->metadata,
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Acciones predeterminadas para IA
    |--------------------------------------------------------------------------
    */

    private function aiQuickActions(
        AIResponse $aiResponse
    ): array {
        $category = mb_strtolower(
            trim(
                (string) $aiResponse->category
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Error interno o baja confianza
        |--------------------------------------------------------------------------
        */

        if (
            $category === 'system'
            || $aiResponse->confidence <= 0
        ) {
            return [
                [
                    'label' => 'Intentar nuevamente',
                    'action' => 'send',
                    'value' => 'necesito ayuda con un problema de TI',
                ],

                $this->redirectAction(
                    'Crear incidencia',
                    'incidencia'
                ),

                [
                    'label' => 'Mostrar menú',
                    'action' => 'send',
                    'value' => 'menu',
                ],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Solicitud
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
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
                    'contraseña',
                    'equipo',
                    'impresora',
                ],
                true
            )
        ) {
            return [
                $this->redirectAction(
                    'Crear solicitud',
                    'solicitud'
                ),

                [
                    'label' => 'Consultar gestiones',
                    'action' => 'send',
                    'value' => 'consultar estado',
                ],

                [
                    'label' => 'Mostrar menú',
                    'action' => 'send',
                    'value' => 'menu',
                ],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Pase menor a 24 horas
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $category,
                [
                    'pase',
                    'pase_temporal',
                    'pase_menor_24h',
                ],
                true
            )
        ) {
            return [
                $this->redirectAction(
                    'Crear pase temporal',
                    'pase_menor_24h'
                ),

                [
                    'label' => 'Mostrar menú',
                    'action' => 'send',
                    'value' => 'menu',
                ],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Autorización mayor a 24 horas
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $category,
                [
                    'autorizacion',
                    'autorización',
                    'memorando',
                    'autorizacion_memorando',
                ],
                true
            )
        ) {
            return [
                $this->redirectAction(
                    'Crear autorización',
                    'autorizacion_memorando'
                ),

                [
                    'label' => 'Mostrar menú',
                    'action' => 'send',
                    'value' => 'menu',
                ],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Problemas técnicos e incidencias
        |--------------------------------------------------------------------------
        */

        return [
            [
                'label' => 'Sigue sin funcionar',
                'action' => 'send',
                'value' => 'sigue sin funcionar',
            ],

            $this->redirectAction(
                'Crear incidencia',
                'incidencia'
            ),

            [
                'label' => 'Mostrar menú',
                'action' => 'send',
                'value' => 'menu',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Respuesta para un módulo específico
    |--------------------------------------------------------------------------
    */

    private function forModule(
        string $key,
        string $message
    ): array {
        $redirect = $this->getRedirect(
            $key
        );

        return [
            'message' => $message,

            'quick_actions' => [
                $this->redirectAction(
                    $redirect['label'] ?? 'Ir al formulario',
                    $key
                ),

                [
                    'label' => 'Consultar estado',
                    'action' => 'send',
                    'value' => 'consultar estado',
                ],

                [
                    'label' => 'Mostrar menú',
                    'action' => 'send',
                    'value' => 'menu',
                ],
            ],

            /*
             * Se conserva para mostrar también el botón principal
             * definido en msg.redirect.
             */
            'redirect' => $redirect,

            'items' => null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Acción de redirección
    |--------------------------------------------------------------------------
    */

    private function redirectAction(
        string $label,
        string $moduleKey
    ): array {
        $redirect = $this->getRedirect(
            $moduleKey
        );

        /*
         * Si la ruta no existe, se devuelve una acción segura
         * que abre nuevamente el menú.
         */
        if (!$redirect || empty($redirect['url'])) {
            return [
                'label' => $label,
                'action' => 'send',
                'value' => 'menu',
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
    */

    private function getRedirect(
        string $key
    ): ?array {
        $module = config(
            "chatbot.modules.{$key}"
        );

        if (
            !is_array($module)
            || empty($module['create'])
            || empty($module['label'])
            || !Route::has($module['create'])
        ) {
            return null;
        }

        return [
            'label' => 'Ir a: '.$module['label'],

            'url' => route(
                $module['create']
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Redirección sugerida según categoría IA
    |--------------------------------------------------------------------------
    */

    private function getRedirectForAICategory(
        AIResponse $aiResponse
    ): ?array {
        $category = mb_strtolower(
            trim(
                (string) $aiResponse->category
            )
        );

        if (
            in_array(
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
                    'contraseña',
                    'equipo',
                    'impresora',
                ],
                true
            )
        ) {
            return $this->getRedirect(
                'solicitud'
            );
        }

        if (
            in_array(
                $category,
                [
                    'pase',
                    'pase_temporal',
                    'pase_menor_24h',
                ],
                true
            )
        ) {
            return $this->getRedirect(
                'pase_menor_24h'
            );
        }

        if (
            in_array(
                $category,
                [
                    'autorizacion',
                    'autorización',
                    'memorando',
                    'autorizacion_memorando',
                ],
                true
            )
        ) {
            return $this->getRedirect(
                'autorizacion_memorando'
            );
        }

        return $this->getRedirect(
            'incidencia'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar acciones creadas por IA
    |--------------------------------------------------------------------------
    */

    private function normalizeQuickActions(
        array $actions
    ): array {
        $normalized = [];

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

            if ($label === '') {
                continue;
            }

            $type = mb_strtolower(
                trim(
                    (string) (
                        $action['action']
                        ?? $action['type']
                        ?? 'send'
                    )
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Acción para enviar mensaje
            |--------------------------------------------------------------------------
            */

            if ($type === 'send') {
                $value = trim(
                    (string) (
                        $action['value']
                        ?? $action['message']
                        ?? $label
                    )
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
            | Redirección
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $type,
                    [
                        'redirect',
                        'link',
                        'url',
                    ],
                    true
                )
            ) {
                $url = trim(
                    (string) (
                        $action['url']
                        ?? $action['href']
                        ?? ''
                    )
                );

                /*
                 * La IA puede devolver un module_key en lugar
                 * de una URL.
                 */
                $moduleKey = trim(
                    (string) (
                        $action['module']
                        ?? $action['module_key']
                        ?? ''
                    )
                );

                if ($url === '' && $moduleKey !== '') {
                    $redirect = $this->getRedirect(
                        $moduleKey
                    );

                    $url = $redirect['url'] ?? '';
                }

                /*
                 * Inferir el módulo desde la etiqueta.
                 */
                if ($url === '') {
                    $moduleKey = $this->inferModuleFromLabel(
                        $label
                    );

                    if ($moduleKey !== null) {
                        $redirect = $this->getRedirect(
                            $moduleKey
                        );

                        $url = $redirect['url'] ?? '';
                    }
                }

                if ($url === '') {
                    continue;
                }

                $normalized[] = [
                    'label' => $label,
                    'action' => 'redirect',
                    'url' => $url,
                ];
            }
        }

        return $normalized;
    }


    /*
    |--------------------------------------------------------------------------
    | Inferir módulo desde etiqueta
    |--------------------------------------------------------------------------
    */

    private function inferModuleFromLabel(
        string $label
    ): ?string {
        $normalizedLabel = mb_strtolower(
            $label
        );

        if (
            str_contains(
                $normalizedLabel,
                'incidencia'
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

        if (
            str_contains(
                $normalizedLabel,
                'pase'
            )
        ) {
            return 'pase_menor_24h';
        }

        if (
            str_contains(
                $normalizedLabel,
                'autorización'
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

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Agregar información de intención
    |--------------------------------------------------------------------------
    */

    private function appendIntent(
        array $response,
        IntentResult $intent
    ): array {
        $response['intent'] = [
            'name' => $intent->intent,
            'score' => $intent->score,
            'confidence' => $intent->confidence,
            'matched' => $intent->matchedKeywords,
            'alternatives' => $intent->alternatives,
        ];

        return $response;
    }


    /*
    |--------------------------------------------------------------------------
    | Acciones rápidas principales
    |--------------------------------------------------------------------------
    */

    private function defaultQuickActions(): array
    {
        return [
            [
                'label' => 'Reportar incidencia',
                'action' => 'send',
                'value' => 'quiero reportar una incidencia',
            ],

            [
                'label' => 'Crear solicitud',
                'action' => 'send',
                'value' => 'quiero crear una solicitud',
            ],

            [
                'label' => 'Consultar estado',
                'action' => 'send',
                'value' => 'consultar estado',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Menú principal con vínculos directos
    |--------------------------------------------------------------------------
    */

    private function mainMenuActions(): array
    {
        return [
            $this->redirectAction(
                'Reporte de incidencia',
                'incidencia'
            ),

            $this->redirectAction(
                'Solicitudes',
                'solicitud'
            ),

            $this->redirectAction(
                'Pase menor a 24 horas',
                'pase_menor_24h'
            ),

            $this->redirectAction(
                'Pase mayor a 24 horas',
                'autorizacion_memorando'
            ),

            [
                'label' => 'Consultar gestiones',
                'action' => 'send',
                'value' => 'consultar estado',
            ],
        ];
    }
}