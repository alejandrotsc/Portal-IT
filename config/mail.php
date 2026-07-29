<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | Define el mailer utilizado por defecto para todos los correos.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),


    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',

            'scheme' => env('MAIL_SCHEME'),

            'url' => env('MAIL_URL'),

            'host' => env(
                'MAIL_HOST',
                '127.0.0.1'
            ),

            'port' => (int) env(
                'MAIL_PORT',
                2525
            ),

            'username' => env(
                'MAIL_USERNAME'
            ),

            'password' => env(
                'MAIL_PASSWORD'
            ),

            /*
             * El timeout SMTP debe ser menor que el timeout del Job.
             *
             * Configuración actual recomendada:
             *
             * - SMTP timeout: 60 segundos
             * - Job timeout: 120 segundos
             * - Queue retry_after: 180 segundos
             */
            'timeout' => (int) env(
                'MAIL_TIMEOUT',
                60
            ),

            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url(
                    (string) env(
                        'APP_URL',
                        'http://localhost'
                    ),
                    PHP_URL_HOST
                )
            ),
        ],


        'ses' => [
            'transport' => 'ses',
        ],


        'postmark' => [
            'transport' => 'postmark',

            // 'message_stream_id' =>
            //     env('POSTMARK_MESSAGE_STREAM_ID'),

            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],


        'resend' => [
            'transport' => 'resend',
        ],


        'sendmail' => [
            'transport' => 'sendmail',

            'path' => env(
                'MAIL_SENDMAIL_PATH',
                '/usr/sbin/sendmail -bs -i'
            ),
        ],


        'log' => [
            'transport' => 'log',

            'channel' => env(
                'MAIL_LOG_CHANNEL'
            ),
        ],


        'array' => [
            'transport' => 'array',
        ],


        /*
         * Se conserva por compatibilidad, pero el Portal TI utiliza
         * MAIL_MAILER=smtp y los reintentos son gestionados por
         * SendTrackedMailJob.
         *
         * No se recomienda configurar MAIL_MAILER=failover en producción
         * mientras el segundo transporte sea "log", porque un correo podría
         * registrarse como exitoso sin haber sido entregado al destinatario.
         */
        'failover' => [
            'transport' => 'failover',

            'mailers' => [
                'smtp',
                'log',
            ],

            'retry_after' => 60,
        ],


        'roundrobin' => [
            'transport' => 'roundrobin',

            'mailers' => [
                'ses',
                'postmark',
            ],

            'retry_after' => 60,
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env(
            'MAIL_FROM_ADDRESS',
            'hello@example.com'
        ),

        'name' => env(
            'MAIL_FROM_NAME',
            env(
                'APP_NAME',
                'Laravel'
            )
        ),
    ],

];