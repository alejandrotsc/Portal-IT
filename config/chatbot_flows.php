<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nodo inicial
    |--------------------------------------------------------------------------
    */

    'start' => 'menu.principal',


    /*
    |--------------------------------------------------------------------------
    | Nodos del asistente interactivo
    |--------------------------------------------------------------------------
    */

    'nodes' => [

        /*
        |--------------------------------------------------------------------------
        | Menú principal
        |--------------------------------------------------------------------------
        */

        'menu.principal' => [
    'message' =>
        '¿En qué puedo ayudarte? Selecciona una opción para orientarte rápidamente.',

    'quick_actions' => [
        [
            'label' => 'Tengo un problema',
            'icon' => 'circle-alert',
            'action' => 'flow',
            'value' => 'problema.menu',
        ],

        [
            'label' => 'Necesito un servicio',
            'icon' => 'wrench',
            'action' => 'flow',
            'value' => 'solicitud.menu',
        ],

        [
            'label' => 'Necesito un pase',
            'icon' => 'contact',
            'action' => 'flow',
            'value' => 'pase.menu',
        ],

        [
            'label' => 'Consultar gestiones',
            'icon' => 'search',
            'action' => 'status',
            'value' => 'gestion.estado',
        ],

        [
            'label' => 'Hacer una pregunta',
            'icon' => 'sparkles',
            'variant' => 'ai',
            'action' => 'flow',
            'value' => 'ai.enable',
        ],
    ],
],


        /*
        |--------------------------------------------------------------------------
        | Menú de problemas
        |--------------------------------------------------------------------------
        */

        'problema.menu' => [
            'message' =>
                '¿Qué equipo o servicio está presentando problemas?',

            'quick_actions' => [
                [
                    'label' => 'Internet o WiFi',
                    'action' => 'flow',
                    'value' => 'problema.internet',
                ],

                [
                    'label' => 'Outlook o correo',
                    'action' => 'flow',
                    'value' => 'problema.correo',
                ],

                [
                    'label' => 'Computadora lenta',
                    'action' => 'flow',
                    'value' => 'problema.lentitud',
                ],

                [
                    'label' => 'No enciende',
                    'action' => 'flow',
                    'value' => 'problema.encendido',
                ],

                [
                    'label' => 'Impresora',
                    'action' => 'flow',
                    'value' => 'problema.impresora',
                ],

                [
                    'label' => 'Sistema o aplicación',
                    'action' => 'flow',
                    'value' => 'problema.sistema',
                ],

                [
                    'label' => 'Teclado, mouse o monitor',
                    'action' => 'flow',
                    'value' => 'problema.periferico',
                ],

                [
                    'label' => 'Otro problema',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Internet
        |--------------------------------------------------------------------------
        */

        'problema.internet' => [
            'message' =>
                "Primero revisemos la conexión.\n\n"
                ."¿El equipo aparece conectado al WiFi o mediante cable de red?",

            'quick_actions' => [
                [
                    'label' => 'Sí aparece conectado',
                    'action' => 'flow',
                    'value' => 'internet.conectado',
                ],

                [
                    'label' => 'No aparece conectado',
                    'action' => 'flow',
                    'value' => 'internet.desconectado',
                ],

                [
                    'label' => 'No estoy seguro',
                    'action' => 'flow',
                    'value' => 'internet.no_seguro',
                ],

                [
                    'label' => 'Volver',
                    'action' => 'flow',
                    'value' => 'problema.menu',
                ],
            ],
        ],

        'internet.conectado' => [
            'message' =>
                "Intenta abrir dos sitios diferentes y verifica si otros compañeros tienen el mismo problema. "
                ."También puedes desconectar y volver a conectar el WiFi.",

            'quick_actions' => [
                [
                    'label' => 'Ya funciona',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Sigue sin funcionar',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],

        'internet.desconectado' => [
            'message' =>
                "Activa el WiFi del equipo y selecciona la red corporativa disponible. "
                ."Si utilizas cable, comprueba que esté conectado firmemente en ambos extremos.",

            'quick_actions' => [
                [
                    'label' => 'Ya funciona',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'No puedo conectarme',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],

        'internet.no_seguro' => [
            'message' =>
                "Busca el icono de red cerca del reloj de Windows. "
                ."Comprueba si muestra una conexión WiFi, un monitor con cable o un aviso de desconexión.",

            'quick_actions' => [
                [
                    'label' => 'Aparece conectado',
                    'action' => 'flow',
                    'value' => 'internet.conectado',
                ],

                [
                    'label' => 'Aparece desconectado',
                    'action' => 'flow',
                    'value' => 'internet.desconectado',
                ],

                [
                    'label' => 'Necesito más ayuda',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Outlook y correo
        |--------------------------------------------------------------------------
        */

        'problema.correo' => [
            'message' =>
                '¿Qué problema estás presentando con Outlook o el correo corporativo?',

            'quick_actions' => [
                [
                    'label' => 'Outlook no abre',
                    'action' => 'flow',
                    'value' => 'correo.no_abre',
                ],

                [
                    'label' => 'No puedo enviar',
                    'action' => 'flow',
                    'value' => 'correo.no_envia',
                ],

                [
                    'label' => 'No recibo correos',
                    'action' => 'flow',
                    'value' => 'correo.no_recibe',
                ],

                [
                    'label' => 'Otro problema',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Volver',
                    'action' => 'flow',
                    'value' => 'problema.menu',
                ],
            ],
        ],

        'correo.no_abre' => [
            'message' =>
                "Verifica que tengas internet. Luego cierra Outlook completamente y vuelve a abrirlo. "
                ."Si aparece un mensaje de error, anótalo o toma una captura.",

            'quick_actions' => [
                [
                    'label' => 'Ya abrió',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Sigue sin abrir',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],

        'correo.no_envia' => [
            'message' =>
                "Comprueba que exista conexión a internet y revisa si el mensaje permanece en la bandeja de salida. "
                ."Verifica también si Outlook muestra algún error.",

            'quick_actions' => [
                [
                    'label' => 'Ya puedo enviar',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Continúa el problema',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],

        'correo.no_recibe' => [
            'message' =>
                "Comprueba la conexión y actualiza la bandeja de entrada. "
                ."Revisa también las carpetas de correo no deseado y otros buzones configurados.",

            'quick_actions' => [
                [
                    'label' => 'Ya recibo correos',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Sigue igual',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Computadora lenta
        |--------------------------------------------------------------------------
        */

        'problema.lentitud' => [
            'message' =>
                "Cierra los programas que no estés utilizando y reinicia el equipo. "
                ."Después verifica si la lentitud ocurre en todo el equipo o solamente en una aplicación.",

            'quick_actions' => [
                [
                    'label' => 'Ya funciona mejor',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Todo sigue lento',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Solo una aplicación',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Equipo que no enciende
        |--------------------------------------------------------------------------
        */

        'problema.encendido' => [
            'message' =>
                "Comprueba que el equipo esté conectado a la corriente. "
                ."Si es una laptop, revisa el cargador y prueba otro tomacorriente seguro. "
                ."Observa si enciende alguna luz.",

            'quick_actions' => [
                [
                    'label' => 'Ya encendió',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Enciende alguna luz',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'No hace nada',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Impresora
        |--------------------------------------------------------------------------
        */

        'problema.impresora' => [
            'message' =>
                "Verifica que la impresora esté encendida, tenga papel y no muestre errores. "
                ."Luego confirma que seleccionaste la impresora correcta antes de imprimir.",

            'quick_actions' => [
                [
                    'label' => 'Ya imprime',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Muestra un error',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Sigue sin imprimir',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Sistemas y aplicaciones
        |--------------------------------------------------------------------------
        */

        'problema.sistema' => [
            'message' =>
                "Cierra y vuelve a abrir la aplicación. Comprueba si tienes conexión y confirma si otros usuarios "
                ."pueden entrar al mismo sistema.",

            'quick_actions' => [
                [
                    'label' => 'Ya funciona',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Muestra un error',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Nadie puede entrar',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Periféricos
        |--------------------------------------------------------------------------
        */

        'problema.periferico' => [
            'message' =>
                "Desconecta y vuelve a conectar el dispositivo. Si utiliza USB, prueba otro puerto disponible. "
                ."No fuerces conectores ni desarmes el equipo.",

            'quick_actions' => [
                [
                    'label' => 'Ya funciona',
                    'action' => 'flow',
                    'value' => 'problema.resuelto',
                ],

                [
                    'label' => 'Sigue sin funcionar',
                    'action' => 'flow',
                    'value' => 'ai.enable',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Problema resuelto
        |--------------------------------------------------------------------------
        */

        'problema.resuelto' => [
            'message' =>
                'Excelente. Me alegra que el problema se haya solucionado. ¿Necesitas algo más?',

            'quick_actions' => [
                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],

                [
                    'label' => 'No, gracias',
                    'action' => 'flow',
                    'value' => 'conversacion.finalizar',
                ],
            ],
        ],

        'conversacion.finalizar' => [
            'message' =>
                'De acuerdo. Si necesitas soporte nuevamente, aquí estaré disponible.',

            'quick_actions' => [
                [
                    'label' => 'Mostrar menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Solicitudes
        |--------------------------------------------------------------------------
        */

        'solicitud.menu' => [
            'message' =>
                'Selecciona el tipo de servicio que necesitas. Después podrás completar la solicitud correspondiente.',

            'quick_actions' => [
                [
                    'label' => 'Equipo o accesorios',
                    'action' => 'redirect',
                    'module' => 'solicitud',
                ],

                [
                    'label' => 'Instalar un programa',
                    'action' => 'redirect',
                    'module' => 'solicitud',
                ],

                [
                    'label' => 'Solicitar acceso',
                    'action' => 'redirect',
                    'module' => 'solicitud',
                ],

                [
                    'label' => 'VPN o acceso remoto',
                    'action' => 'redirect',
                    'module' => 'solicitud',
                ],

                [
                    'label' => 'Cuenta o contraseña',
                    'action' => 'redirect',
                    'module' => 'solicitud',
                ],

                [
                    'label' => 'Otra solicitud',
                    'action' => 'redirect',
                    'module' => 'solicitud',
                ],

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Pases
        |--------------------------------------------------------------------------
        */

        'pase.menu' => [
            'message' =>
                '¿Durante cuánto tiempo necesita acceso la persona?',

            'quick_actions' => [
                [
                    'label' => 'Menos de 24 horas',
                    'action' => 'redirect',
                    'module' => 'pase_menor_24h',
                ],

                [
                    'label' => 'Más de 24 horas',
                    'action' => 'redirect',
                    'module' => 'autorizacion_memorando',
                ],

                [
                    'label' => 'No estoy seguro',
                    'action' => 'flow',
                    'value' => 'pase.explicacion',
                ],

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],
            ],
        ],

        'pase.explicacion' => [
            'message' =>
                "Utiliza Pase menor a 24 horas si el acceso será únicamente por unas horas o un día. "
                ."Si durará más de 24 horas, corresponde una autorización mediante Pase mayor a 24 horas.",

            'quick_actions' => [
                [
                    'label' => 'Menos de 24 horas',
                    'action' => 'redirect',
                    'module' => 'pase_menor_24h',
                ],

                [
                    'label' => 'Más de 24 horas',
                    'action' => 'redirect',
                    'module' => 'autorizacion_memorando',
                ],

                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Activar consulta con IA
        |--------------------------------------------------------------------------
        */

        'ai.enable' => [
            'message' =>
                'Describe el problema o la pregunta con tus propias palabras. Puedes incluir el mensaje de error y lo que ya intentaste.',

            'mode' => 'ai',

            'quick_actions' => [
                [
                    'label' => 'Volver al menú',
                    'action' => 'flow',
                    'value' => 'menu.principal',
                ],

                [
                    'label' => 'Reportar incidencia',
                    'action' => 'redirect',
                    'module' => 'incidencia',
                ],
            ],
        ],

    ],

];