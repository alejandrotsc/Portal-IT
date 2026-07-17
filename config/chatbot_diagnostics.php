<?php

return [

    'minimum_score'=>1,

    'diagnosticos'=>[

        'pc_no_enciende'=>[
            'keywords'=>[
                'no prende'=>5,
                'no enciende'=>5,
                'no arranca'=>4,
                'no inicia'=>4,
                'pantalla negra'=>4,
                'no da señal'=>4,
                'esta muerto'=>3,
                'murio mi pc'=>5,
                'murio la computadora'=>5,
                'mi computadora no prende'=>5,
                'mi computadora no enciende'=>5,
                'mi pc no prende'=>5,
                'mi pc no enciende'=>5,
            ],

            'message'=>
                'parece que el equipo no está encendiendo correctamente.',

            'steps'=>[
                'Verifica que el cable de corriente esté conectado correctamente',
                'Prueba otro tomacorriente',
                'Si es laptop, conecta el cargador unos minutos antes de intentar encenderla',
            ],
        ],

        'internet'=>[
            'keywords'=>[
                'sin internet'=>5,
                'no tengo internet'=>5,
                'no hay internet'=>5,
                'no hay wifi'=>5,
                'wifi no funciona'=>4,
                'no conecta'=>3,
                'sin red'=>4,
                'no carga paginas'=>4,
                'internet lento'=>3,
            ],

            'message'=>
                'parece un problema de conexión a internet o red.',

            'steps'=>[
                'Verifica que el cable de red esté conectado',
                'Si utilizas WiFi, desactiva y activa nuevamente la conexión',
                'Confirma si otros compañeros tienen el mismo problema',
            ],
        ],

        'correo'=>[
            'keywords'=>[
                'correo no funciona'=>5,
                'outlook no abre'=>5,
                'no me llegan correos'=>4,
                'no puedo enviar correo'=>5,
                'no manda correo'=>4,
            ],

            'message'=>
                'parece un problema con el correo electrónico.',

            'steps'=>[
                'Verifica que tengas conexión a internet',
                'Revisa la carpeta de correo no deseado',
                'Confirma que tu buzón tenga espacio disponible',
            ],
        ],

        'equipo_lento'=>[
            'keywords'=>[
                'esta lento'=>4,
                'muy lento'=>5,
                'va lento'=>4,
                'se congela'=>4,
                'se traba'=>4,
                'se queda pensando'=>3,
            ],

            'message'=>
                'parece que el equipo presenta lentitud.',

            'steps'=>[
                'Cierra programas que no estés utilizando',
                'Reinicia el equipo',
                'Verifica si el problema ocurre con todos los programas o solo uno',
            ],
        ],

        'impresora'=>[
            'keywords'=>[
                'no imprime'=>5,
                'impresora no funciona'=>5,
                'impresora atascada'=>5,
                'no saca copias'=>4,
                'papel atascado'=>4,
            ],

            'message'=>
                'parece un problema con la impresora.',

            'steps'=>[
                'Verifica que tenga papel',
                'Confirma que esté encendida y conectada',
                'Revisa si existe algún mensaje de error en la pantalla de la impresora',
            ],
        ],

        'sistema'=>[
            'keywords'=>[
                'sistema no abre'=>5,
                'no puedo entrar al sistema'=>5,
                'sistema caido'=>5,
                'sistema no responde'=>4,
            ],

            'message'=>
                'parece que existe un problema con un sistema interno.',

            'steps'=>[
                'Cierra sesión e intenta ingresar nuevamente',
                'Actualiza la página o reinicia la aplicación',
                'Consulta si otros usuarios presentan el mismo problema',
            ],
        ],

        'perifericos'=>[
            'keywords'=>[
                'mouse no funciona'=>5,
                'mouse no sirve'=>5,
                'teclado no funciona'=>5,
                'teclado no sirve'=>5,
            ],

            'message'=>
                'parece un problema con un dispositivo conectado.',

            'steps'=>[
                'Verifica que el dispositivo esté conectado correctamente',
                'Prueba otro puerto USB',
                'Si es inalámbrico, revisa la batería',
            ],
        ],

        'virus'=>[
            'keywords'=>[
                'virus'=>5,
                'ventanas emergentes'=>5,
                'se abren ventanas solas'=>5,
                'publicidad extraña'=>4,
            ],

            'message'=>
                'parece un comportamiento extraño relacionado con software no deseado.',

            'steps'=>[
                'Evita ingresar contraseñas mientras revisamos el equipo',
                'No hagas clic en ventanas sospechosas',
                'Solicita una revisión del equipo si continúa el problema',
            ],
        ],

    ],

];