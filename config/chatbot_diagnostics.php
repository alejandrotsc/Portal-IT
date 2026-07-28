<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Puntaje mínimo
    |--------------------------------------------------------------------------
    */

    'minimum_score' => 1,


    /*
    |--------------------------------------------------------------------------
    | Diagnósticos simples para usuarios
    |--------------------------------------------------------------------------
    |
    | Las recomendaciones deben poder realizarse sin conocimientos técnicos.
    | No deben incluir comandos, consolas, cambios avanzados ni configuraciones
    | internas del sistema operativo.
    |
    */

    'diagnosticos' => [

        /*
        |--------------------------------------------------------------------------
        | Equipo no enciende
        |--------------------------------------------------------------------------
        */

        'pc_no_enciende' => [

            'keywords' => [

                'no prende' => 5,
                'no enciende' => 5,
                'no arranca' => 4,
                'no inicia' => 4,
                'pantalla negra' => 4,
                'no da señal' => 4,
                'no hace nada' => 4,
                'no responde al encender' => 5,
                'esta muerto' => 3,
                'murio mi pc' => 5,
                'murio la computadora' => 5,
                'mi computadora no prende' => 5,
                'mi computadora no enciende' => 5,
                'mi pc no prende' => 5,
                'mi pc no enciende' => 5,
                'la laptop no prende' => 5,
                'la laptop no enciende' => 5,

            ],

            'message' =>
                'Parece que el equipo no está encendiendo correctamente.',

            'steps' => [

                'Verifica que el cable de corriente o cargador esté bien conectado.',
                'Prueba conectarlo en otro tomacorriente.',
                'Si es una laptop, déjala conectada al cargador durante unos minutos.',
                'Intenta encenderla nuevamente.',

            ],

        ],


        /*
        |--------------------------------------------------------------------------
        | Internet o red
        |--------------------------------------------------------------------------
        */

        'internet' => [

            'keywords' => [

                'sin internet' => 5,
                'no tengo internet' => 5,
                'no hay internet' => 5,
                'no hay wifi' => 5,
                'wifi no funciona' => 4,
                'wifi desconectado' => 4,
                'no conecta al wifi' => 5,
                'no conecta' => 3,
                'sin red' => 4,
                'red no funciona' => 4,
                'no carga paginas' => 4,
                'internet lento' => 3,
                'internet se cae' => 4,
                'se desconecta el internet' => 5,

            ],

            'message' =>
                'Parece que hay un problema con la conexión a internet o la red.',

            'steps' => [

                'Verifica que el cable de red esté conectado, si utilizas conexión por cable.',
                'Si utilizas WiFi, apágalo y vuelve a activarlo.',
                'Espera unos segundos e intenta abrir nuevamente la página o sistema.',
                'Confirma si otros compañeros tienen el mismo problema.',

            ],

        ],


        /*
        |--------------------------------------------------------------------------
        | Correo electrónico
        |--------------------------------------------------------------------------
        */

        'correo' => [

            'keywords' => [

                'correo no funciona' => 5,
                'outlook no abre' => 5,
                'outlook no funciona' => 5,
                'no me llegan correos' => 4,
                'no recibo correos' => 4,
                'no puedo enviar correo' => 5,
                'no puedo enviar correos' => 5,
                'no manda correo' => 4,
                'correo atascado' => 4,
                'correo no sincroniza' => 5,
                'outlook no sincroniza' => 5,

            ],

            'message' =>
                'Parece que hay un problema con el correo electrónico.',

            'steps' => [

                'Verifica que tengas conexión a internet.',
                'Cierra Outlook o la aplicación de correo y vuelve a abrirla.',
                'Revisa la carpeta de correo no deseado si esperas un mensaje.',
                'Confirma si puedes enviar o recibir otros correos.',

            ],

        ],


        /*
        |--------------------------------------------------------------------------
        | Equipo lento
        |--------------------------------------------------------------------------
        */

        'equipo_lento' => [

            'keywords' => [

                'esta lento' => 4,
                'muy lento' => 5,
                'va lento' => 4,
                'computadora lenta' => 5,
                'pc lenta' => 5,
                'laptop lenta' => 5,
                'se congela' => 4,
                'se traba' => 4,
                'se queda pensando' => 3,
                'tarda mucho' => 3,
                'todo esta lento' => 5,

            ],

            'message' =>
                'Parece que el equipo está funcionando con lentitud.',

            'steps' => [

                'Guarda el trabajo que tengas abierto.',
                'Cierra los programas que no estés utilizando.',
                'Reinicia el equipo.',
                'Verifica si la lentitud ocurre en todos los programas o solamente en uno.',

            ],

        ],


        /*
        |--------------------------------------------------------------------------
        | Impresora
        |--------------------------------------------------------------------------
        */

        'impresora' => [

            'keywords' => [

                'no imprime' => 5,
                'impresora no funciona' => 5,
                'impresora no responde' => 5,
                'impresora atascada' => 5,
                'no saca copias' => 4,
                'papel atascado' => 4,
                'impresora sin conexion' => 4,
                'impresora desconectada' => 4,
                'documento no imprime' => 5,
                'impresion atascada' => 4,

            ],

            'message' =>
                'Parece que hay un problema con la impresora.',

            'steps' => [

                'Verifica que la impresora esté encendida.',
                'Confirma que tenga papel disponible.',
                'Revisa si aparece algún mensaje de error en su pantalla.',
                'Intenta imprimir nuevamente una sola vez.',

            ],

        ],


        /*
        |--------------------------------------------------------------------------
        | Sistema o aplicación
        |--------------------------------------------------------------------------
        */

        'sistema' => [

            'keywords' => [

                'sistema no abre' => 5,
                'no puedo entrar al sistema' => 5,
                'no puedo ingresar al sistema' => 5,
                'sistema caido' => 5,
                'sistema no responde' => 4,
                'sistema no funciona' => 5,
                'aplicacion no abre' => 5,
                'programa no abre' => 5,
                'pagina no carga' => 4,
                'sesion no inicia' => 4,

            ],

            'message' =>
                'Parece que hay un problema con un sistema o una aplicación.',

            'steps' => [

                'Cierra el sistema o la aplicación e intenta abrirla nuevamente.',
                'Si es una página web, actualízala una vez.',
                'Verifica que tengas conexión a internet.',
                'Consulta si otros usuarios presentan el mismo problema.',

            ],

        ],


        /*
        |--------------------------------------------------------------------------
        | Periféricos
        |--------------------------------------------------------------------------
        */

        'perifericos' => [

            'keywords' => [

                'mouse no funciona' => 5,
                'mouse no sirve' => 5,
                'mouse no responde' => 5,
                'teclado no funciona' => 5,
                'teclado no sirve' => 5,
                'teclado no responde' => 5,
                'camara no funciona' => 5,
                'microfono no funciona' => 5,
                'audifonos no funcionan' => 5,
                'usb no funciona' => 4,

            ],

            'message' =>
                'Parece que hay un problema con un dispositivo conectado al equipo.',

            'steps' => [

                'Verifica que el dispositivo esté conectado correctamente.',
                'Desconéctalo y vuelve a conectarlo.',
                'Prueba otro puerto disponible.',
                'Si es inalámbrico, revisa que tenga batería o carga.',

            ],

        ],


        /*
        |--------------------------------------------------------------------------
        | Posible software no deseado
        |--------------------------------------------------------------------------
        */

        'virus' => [

            'keywords' => [

                'virus' => 5,
                'tengo un virus' => 5,
                'ventanas emergentes' => 5,
                'se abren ventanas solas' => 5,
                'publicidad extraña' => 4,
                'mensajes extraños' => 4,
                'programas desconocidos' => 4,
                'archivos raros' => 3,
                'computadora infectada' => 5,
                'posible virus' => 5,

            ],

            'message' =>
                'Parece que el equipo presenta un comportamiento extraño que debe ser revisado por TI.',

            'steps' => [

                'No ingreses contraseñas ni información sensible.',
                'No hagas clic en ventanas, mensajes o enlaces desconocidos.',
                'Desconecta cualquier memoria USB que no estés utilizando.',
                'Registra una incidencia para que el equipo de TI revise el dispositivo.',

            ],

        ],

    ],

];