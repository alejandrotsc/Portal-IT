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
    |
    | Las rutas deben existir en routes/web.php
    |
    */

    'modules' => [


        'incidencia' => [

            'label' => 'Reportar incidencia',

            'create' => 'incidencias.create',

            'index' => 'incidencias.index',

        ],




        'solicitud' => [

            'label' => 'Solicitud de servicio',

            'create' => 'solicitudes.create',

            'index' => 'solicitudes.create',

        ],




        'pase_menor_24h' => [

            'label' => 'Pase de acceso menor a 24 horas',

            'create' => 'memorandos.pase_temporal',

            'index' => 'memorandos.historico',

        ],





        'autorizacion_memorando' => [

            'label' => 'Autorización por memorando',

            'create' => 'memorandos.autorizacion',

            'index' => 'memorandos.historico',

        ],



    ],





    /*
    |--------------------------------------------------------------------------
    | Palabras clave por intención
    |--------------------------------------------------------------------------
    */

    'keywords' => [





        'incidencia' => [


            'incidencia',

            'falla',

            'fallo',

            'error',

            'problema',

            'no funciona',

            'no sirve',

            'no anda',

            'esta fallando',

            'dañado',

            'roto',

            'lento',

            'se congela',

            'pantalla negra',

            'pantalla azul',

            'no responde',

            'no prende',

            'no enciende',

            'no arranca',

            'sin internet',

            'sin wifi',

            'no imprime',

            'correo no funciona',

            'outlook no abre',

            'sistema caido',

            'virus',

            'reportar problema',


        ],





        'solicitud' => [


            'solicitud',

            'solicitar',

            'necesito',

            'requiero',

            'quiero pedir',

            'nuevo equipo',

            'equipo nuevo',

            'cambio de equipo',

            'instalar',

            'instalacion',

            'software',

            'licencia',

            'crear usuario',

            'nuevo usuario',

            'cuenta nueva',

            'correo nuevo',

            'acceso',

            'vpn',

            'habilitar',


        ],






        'pase_menor_24h' => [


            'pase',

            'pase temporal',

            'acceso temporal',

            'permiso temporal',

            'visita',

            'proveedor',

            'tecnico externo',

            'menos de 24 horas',

            'menor a 24 horas',

            'solo por hoy',

            'entrar hoy',

            'ingreso temporal',


        ],






        'autorizacion_memorando' => [


            'memorando',

            'autorizacion',

            'autorizar',

            'acceso permanente',

            'mas de 24 horas',

            'varios dias',

            'una semana',

            'ingreso prolongado',

            'prestamo de equipo',

            'sacar equipo',


        ],






        'consultar_estado' => [


            'estado',

            'seguimiento',

            'como va',

            'que paso',

            'mi solicitud',

            'mi incidencia',

            'mi memorando',

            'mis gestiones',

            'ya revisaron',

            'ya aprobaron',

            'fue aprobado',


        ],







        'cierre' => [


            'gracias',

            'muchas gracias',

            'ya funciona',

            'ya quedo',

            'resuelto',

            'listo',

            'perfecto gracias',


        ],






        'saludo' => [


            'hola',

            'buenos dias',

            'buenas tardes',

            'buenas noches',

            'que tal',

            'ayuda',

            'menu',


        ],




    ],






    /*
    |--------------------------------------------------------------------------
    | Inteligencia Artificial
    |--------------------------------------------------------------------------
    */

    'ai' => [


        'url' => env(

            'CHATBOT_AI_URL',

            'http://127.0.0.1:11434/api/generate'

        ),



        'model' => env(

            'CHATBOT_AI_MODEL',

            'llama3.2'

        ),



        'timeout' => 60,


    ],



];