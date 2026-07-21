<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración general
    |--------------------------------------------------------------------------
    */

    'min_score' => 1,

    'fallback_name' => 'usuario',


    /*
    |--------------------------------------------------------------------------
    | Módulos del Portal TI
    |--------------------------------------------------------------------------
    */

    'modules' => [

    'incidencia' => [
        'label' => 'Reporte de incidencia',
        'create' => 'incidencias.create',
        'index' => 'mis-incidencias',
    ],

    'solicitud' => [
        'label' => 'Solicitudes de servicio',
        'create' => 'solicitudes.create',
        'index' => 'mis-solicitudes',
    ],

    'pase_menor_24h' => [
        'label' => 'Pase menor a 24 horas',
        'create' => 'memorandos.pase_temporal',
        'index' => 'memorandos.mis-pases',
    ],

    'autorizacion_memorando' => [
        'label' => 'Pase mayor a 24 horas',
        'create' => 'memorandos.autorizacion',
        'index' => 'memorandos.mis-pases',
    ],

],


    /*
    |--------------------------------------------------------------------------
    | Palabras y frases por intención
    |--------------------------------------------------------------------------
    */

    'keywords' => [

        /*
        |--------------------------------------------------------------------------
        | Reporte de incidencia
        |--------------------------------------------------------------------------
        */

        'incidencia' => [

            /*
             * Comandos directos para registrar incidencias.
             */
            'quiero reportar una incidencia',
            'quiero crear una incidencia',
            'quiero reportar un problema',
            'reportar incidencia',
            'reporte de incidencia',
            'reportar problema',
            'crear incidencia',
            'abrir incidencia',
            'registrar incidencia',
            'nueva incidencia',
            'tengo una incidencia',
            'tengo un problema',
            'necesito reportar un problema',

            /*
             * Palabras generales.
             */
            'incidencia',
            'falla',
            'fallo',
            'error',
            'problema',

            /*
             * Funcionamiento general.
             */
            'no funciona',
            'no me funciona',
            'sigue sin funcionar',
            'continua sin funcionar',
            'todavia no funciona',
            'no sirve',
            'no anda',
            'no jala',
            'esta fallando',
            'dejo de funcionar',
            'dañado',
            'roto',

            /*
             * Lentitud.
             */
            'muy lento',
            'esta lento',
            'esta muy lento',
            'mi computadora esta lenta',
            'mi laptop esta lenta',
            'se congela',
            'se traba',
            'se queda pegado',
            'se queda pensando',
            'no responde',

            /*
             * Pantalla.
             */
            'pantalla negra',
            'pantalla azul',
            'no da imagen',
            'no da señal',

            /*
             * Encendido.
             */
            'no prende',
            'no enciende',
            'no arranca',
            'no inicia',
            'computadora no enciende',
            'laptop no enciende',

            /*
             * Internet y red.
             */
            'sin internet',
            'no tengo internet',
            'no hay internet',
            'internet no funciona',
            'internet lento',
            'sin wifi',
            'no tengo wifi',
            'wifi no funciona',
            'no conecta al wifi',
            'no conecta a la red',
            'sin red',

            /*
             * Impresoras.
             */
            'no imprime',
            'impresora no funciona',
            'impresora no imprime',
            'impresora atascada',
            'papel atascado',
            'no saca copias',

            /*
             * Correo y Outlook.
             */
            'correo no funciona',
            'correo no abre',
            'outlook no abre',
            'outlook no funciona',
            'outlook se queda cargando',
            'no puedo enviar correos',
            'no puedo enviar correo',
            'no me llegan correos',
            'no recibo correos',
            'correo bloqueado',

            /*
             * Sistemas internos.
             */
            'sistema caido',
            'sistema no abre',
            'sistema no funciona',
            'sistema no responde',
            'no puedo entrar al sistema',

            /*
             * Seguridad.
             */
            'virus',
            'posible virus',
            'archivo sospechoso',
            'ventanas extrañas',
        ],


        /*
        |--------------------------------------------------------------------------
        | Solicitudes de servicio
        |--------------------------------------------------------------------------
        */

        'solicitud' => [

            'quiero crear una solicitud',
            'crear solicitud',
            'nueva solicitud',
            'solicitud de servicio',
            'hacer una solicitud',
            'necesito hacer una solicitud',
            'solicitud',
            'solicitar',

            /*
             * Equipos y accesorios.
             */
            'computadora o accesorios',
            'necesito un accesorio',
            'necesito un teclado',
            'necesito un mouse',
            'necesito una pantalla',
            'necesito un monitor',
            'necesito audifonos',
            'requiero un equipo',
            'necesito un equipo',
            'quiero pedir un equipo',
            'nuevo equipo',
            'equipo nuevo',
            'cambio de equipo',
            'reemplazo de equipo',
            'configurar equipo',

            /*
             * Programas y licencias.
             */
            'instalar programa',
            'instalar un programa',
            'instalar software',
            'instalacion de programa',
            'instalacion de software',
            'necesito un programa',
            'necesito una aplicacion',
            'software nuevo',
            'licencia de software',
            'solicitar licencia',

            /*
             * Usuarios y cuentas.
             */
            'crear usuario',
            'nuevo usuario',
            'cuenta nueva',
            'correo nuevo',

            /*
             * Accesos.
             */
            'solicitar acceso',
            'necesito acceso',
            'acceso a sistema',
            'acceso a carpeta',
            'acceso a recurso de red',
            'permisos de sistema',
            'habilitar acceso',

            /*
             * VPN.
             */
            'solicitar vpn',
            'necesito vpn',
            'acceso remoto',
            'trabajar desde casa',
            'conectarme desde fuera',

            /*
             * Contraseñas.
             */
            'restablecer contraseña',
            'cambiar contraseña',
            'olvide mi contraseña',
            'desbloquear cuenta',
            'cuenta bloqueada',

            /*
             * Configuración de impresora.
             */
            'configurar impresora',
            'conectar impresora',
            'agregar impresora',
        ],


        /*
        |--------------------------------------------------------------------------
        | Pase menor a 24 horas
        |--------------------------------------------------------------------------
        */

        'pase_menor_24h' => [

            'quiero crear un pase temporal',
            'crear pase temporal',
            'solicitar pase temporal',
            'pase temporal',

            'pase menor a 24 horas',
            'pase menor de 24 horas',
            'menos de 24 horas',
            'menor a 24 horas',

            'acceso temporal',
            'permiso temporal',
            'ingreso temporal',

            'visita temporal',
            'visita de proveedor',
            'visita',
            'proveedor',
            'tecnico externo',

            'solo por hoy',
            'entrar hoy',
            'ingresar hoy',
            'acceso por unas horas',

            'pase',
        ],


        /*
        |--------------------------------------------------------------------------
        | Pase mayor a 24 horas o autorización
        |--------------------------------------------------------------------------
        */

        'autorizacion_memorando' => [

            'crear memorando',
            'solicitar autorizacion',
            'crear autorizacion',

            'autorizacion por memorando',
            'memorando de autorizacion',
            'memorando',

            'autorizacion',
            'autorizar',

            'pase mayor a 24 horas',
            'pase mayor de 24 horas',
            'mas de 24 horas',
            'mayor a 24 horas',

            'varios dias',
            'por varios dias',
            'una semana',
            'varias semanas',
            'ingreso prolongado',
            'acceso prolongado',
            'acceso permanente',

            'prestamo de equipo',
            'sacar equipo',
            'retiro de equipo',
            'ingreso de equipo',
        ],


        /*
        |--------------------------------------------------------------------------
        | Consultar gestiones
        |--------------------------------------------------------------------------
        */

        'consultar_estado' => [

            'consultar estado',
            'ver estado',
            'consultar gestiones',
            'ver mis gestiones',

            'estado de mis gestiones',
            'estado de mi solicitud',
            'estado de mi incidencia',
            'estado de mi memorando',

            'como va mi solicitud',
            'como va mi incidencia',
            'como va mi memorando',
            'como va mi gestion',

            'que paso con mi solicitud',
            'que paso con mi incidencia',
            'que paso con mi memorando',

            'mis gestiones',
            'mis solicitudes',
            'mis incidencias',
            'mis memorandos',

            'seguimiento',
            'dar seguimiento',

            'ya revisaron',
            'ya aprobaron',
            'fue aprobado',
            'fue rechazada',
            'fue rechazado',
        ],


        /*
        |--------------------------------------------------------------------------
        | Menú
        |--------------------------------------------------------------------------
        */

        'menu' => [

            'menu',
            'mostrar menu',
            'volver al menu',
            'menu principal',

            'ver opciones',
            'mostrar opciones',
            'volver a las opciones',

            'que opciones hay',
            'que puedo hacer',
            'en que me puedes ayudar',
        ],


        /*
        |--------------------------------------------------------------------------
        | Cierre
        |--------------------------------------------------------------------------
        */

        'cierre' => [

            'muchas gracias',
            'gracias',

            'ya funciona',
            'ya funciono',
            'ya quedo',
            'ya se resolvio',

            'problema resuelto',
            'todo resuelto',
            'perfecto gracias',
            'listo gracias',
            'resuelto',
        ],


        /*
        |--------------------------------------------------------------------------
        | Saludos
        |--------------------------------------------------------------------------
        */

        'saludo' => [

            'buenos dias',
            'buenas tardes',
            'buenas noches',
            'que tal',
            'hola',
            'hola asistente',
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Configuración optimizada de Ollama
    |--------------------------------------------------------------------------
    */

    'ai' => [

        /*
        |--------------------------------------------------------------------------
        | Servidor y modelo
        |--------------------------------------------------------------------------
        */

        'url' => env(
            'CHATBOT_AI_URL',
            'http://127.0.0.1:11434/api/chat'
        ),

        'model' => env(
            'CHATBOT_AI_MODEL',
            'llama3.2'
        ),


        /*
        |--------------------------------------------------------------------------
        | Conexión
        |--------------------------------------------------------------------------
        */

        'connect_timeout' => (int) env(
            'CHATBOT_AI_CONNECT_TIMEOUT',
            3
        ),

        'timeout' => (int) env(
            'CHATBOT_AI_TIMEOUT',
            180
        ),


        /*
        |--------------------------------------------------------------------------
        | Historial
        |--------------------------------------------------------------------------
        |
        | Cuatro mensajes cortos aportan continuidad sin enviar
        | demasiado texto al modelo.
        |
        */

        'history_limit' => (int) env(
            'CHATBOT_AI_HISTORY_LIMIT',
            4
        ),

        'history_message_length' => (int) env(
            'CHATBOT_AI_HISTORY_MESSAGE_LENGTH',
            400
        ),


        /*
        |--------------------------------------------------------------------------
        | Modelo cargado en memoria
        |--------------------------------------------------------------------------
        |
        | -1 solicita mantener el modelo cargado mientras Ollama
        | continúe en funcionamiento.
        |
        */

        'keep_alive' => env(
            'CHATBOT_AI_KEEP_ALIVE',
            '-1'
        ),


        /*
        |--------------------------------------------------------------------------
        | Generación
        |--------------------------------------------------------------------------
        */

        'temperature' => (float) env(
            'CHATBOT_AI_TEMPERATURE',
            0.1
        ),

        'top_p' => (float) env(
            'CHATBOT_AI_TOP_P',
            0.85
        ),

        /*
         * Recomendado para el i7 de sexta generación.
         */
        'num_ctx' => (int) env(
            'CHATBOT_AI_NUM_CTX',
            2048
        ),

        /*
         * Mantiene las respuestas breves para que terminen rápidamente.
         */
        'num_predict' => (int) env(
            'CHATBOT_AI_NUM_PREDICT',
            120
        ),

        'repeat_penalty' => (float) env(
            'CHATBOT_AI_REPEAT_PENALTY',
            1.1
        ),

    ],

];