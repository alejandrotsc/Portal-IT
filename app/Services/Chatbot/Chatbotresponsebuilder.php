<?php

namespace App\Services\Chatbot;

use App\Services\Chatbot\AI\AIResponse;
use Illuminate\Support\Facades\Route;
use App\Services\Chatbot\Diagnostics\DiagnosticEngine;


/**
 * Construye las respuestas finales del asistente virtual TI.
 *
 * Convierte:
 *
 * IntentResult
 * DiagnosticEngine
 * AIResponse
 *
 * en una respuesta lista para frontend.
 */
class ChatbotResponseBuilder
{


    public function __construct(

        private readonly DiagnosticEngine $diagnosticEngine

    ) {}









    public function build(

        IntentResult $intent,

        string $userName,

        string $message = '',

        ?AIResponse $aiResponse = null

    ): array {



        /*
        |--------------------------------------------------------------------------
        | Diagnóstico técnico
        |--------------------------------------------------------------------------
        */


        if(
            $intent->is('incidencia')
        ){


            $diagnostic =

                $this->diagnosticEngine->diagnose(

                    $message

                );





            if($diagnostic){


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
        | Respuestas controladas
        |--------------------------------------------------------------------------
        */


        $response = match($intent->intent){





            'incidencia' =>

                $this->forModule(

                    'incidencia',

                    "Entiendo {$userName}. "
                    ."Puedes registrar la incidencia para que soporte pueda revisar el problema."

                ),






            'solicitud' =>

                $this->forModule(

                    'solicitud',

                    "Perfecto {$userName}. "
                    ."Puedes crear una solicitud de servicio para equipos, accesos o software."

                ),






            'pase_menor_24h' =>

                $this->forModule(

                    'pase_menor_24h',

                    "Para accesos menores a 24 horas debes gestionar un pase temporal."

                ),






            'autorizacion_memorando' =>

                $this->forModule(

                    'autorizacion_memorando',

                    "Para accesos mayores a 24 horas se requiere una autorización mediante memorando."

                ),






            'saludo' => [

                'message'=>
                    "Hola {$userName}. ¿En qué puedo ayudarte?",


                'quick_actions'=>
                    $this->defaultQuickActions(),


                'redirect'=>null,


                'items'=>null,


            ],






            'cierre'=>[


                'message'=>
                    "Excelente {$userName}. Me alegra saber que quedó resuelto.",


                'quick_actions'=>
                    $this->defaultQuickActions(),


                'redirect'=>null,


                'items'=>null,


            ],







            'menu'=>[


                'message'=>
                    "Estas son las opciones disponibles:",


                'quick_actions'=>
                    $this->defaultQuickActions(),


                'redirect'=>null,


                'items'=>null,


            ],







            'ai'=>[


                'message'=>

                    $aiResponse?->message
                    ??
                    'No pude obtener una respuesta.',



                'quick_actions'=>

                    !empty($aiResponse?->quickActions)

                    ?

                    $aiResponse->quickActions

                    :

                    $this->defaultQuickActions(),



                'redirect'=>null,


                'items'=>null,



                'ai'=>[


                    'category'=>
                        $aiResponse?->category,


                    'confidence'=>
                        $aiResponse?->confidence,


                    'metadata'=>
                        $aiResponse?->metadata,


                ],


            ],







            default=>[


                'message'=>

                    "No estoy seguro de haber entendido tu solicitud {$userName}. Selecciona una opción:",



                'quick_actions'=>
                    $this->defaultQuickActions(),



                'redirect'=>null,


                'items'=>null,


            ],


        };







        return $this->appendIntent(

            $response,

            $intent

        );


    }












    private function buildDiagnosticResponse(

        array $diagnostic,

        string $userName

    ): array {


        $steps='';




        foreach(

            $diagnostic['steps'] ?? []

            as $step

        ){


            $steps .= "\n• ".$step;


        }







        return [


            'message'=>

                "{$userName}, {$diagnostic['message']}"

                .

                (

                    $steps

                    ?

                    "\n\nPuedes probar:"
                    .$steps

                    :

                    ''

                ),




            'quick_actions'=>[


                [

                    'label'=>'Crear incidencia',

                    'action'=>'redirect',

                ],



                [

                    'label'=>'Consultar mis gestiones',

                    'action'=>'send',

                    'value'=>'consultar estado'

                ],



                [

                    'label'=>'Mostrar menú',

                    'action'=>'send',

                    'value'=>'menu'

                ],


            ],




            'redirect'=>

                $this->getRedirect(

                    'incidencia'

                ),




            'items'=>null,



            'diagnostic'=>[

                'key'=>
                    $diagnostic['key'],

                'score'=>
                    $diagnostic['score'],

                'matched'=>
                    $diagnostic['matched'],

            ],


        ];

    }









    private function forModule(

        string $key,

        string $message

    ): array {


        return [


            'message'=>$message,


            'quick_actions'=>[


                [

                    'label'=>'Ir al formulario',

                    'action'=>'redirect'

                ],



                [

                    'label'=>'Consultar estado',

                    'action'=>'send',

                    'value'=>'consultar estado'

                ],



                [

                    'label'=>'Mostrar menú',

                    'action'=>'send',

                    'value'=>'menu'

                ],


            ],



            'redirect'=>

                $this->getRedirect($key),



            'items'=>null,


        ];


    }









    private function getRedirect(

        string $key

    ): ?array {


        $module =

            config(

                "chatbot.modules.$key"

            );




        if(

            !$module

            ||

            !Route::has(

                $module['create']

            )

        ){

            return null;

        }




        return [


            'label'=>

                'Ir a: '.$module['label'],



            'url'=>

                route(

                    $module['create']

                ),


        ];

    }









    private function appendIntent(

        array $response,

        IntentResult $intent

    ): array {


        $response['intent']=[


            'name'=>

                $intent->intent,


            'score'=>

                $intent->score,


            'confidence'=>

                $intent->confidence,


            'matched'=>

                $intent->matchedKeywords,


        ];



        return $response;


    }









    private function defaultQuickActions(): array
    {


        return [


            [

                'label'=>'Reportar incidencia',

                'action'=>'send',

                'value'=>'quiero reportar una incidencia'

            ],



            [

                'label'=>'Crear solicitud',

                'action'=>'send',

                'value'=>'quiero crear una solicitud'

            ],



            [

                'label'=>'Consultar estado',

                'action'=>'send',

                'value'=>'consultar estado'

            ],


        ];


    }



}