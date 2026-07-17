<?php

namespace App\Services\Chatbot;

use App\Services\Chatbot\AI\AIServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;


/**
 * Orquestador principal del Asistente Virtual TI.
 *
 * Flujo:
 *
 * Usuario
 *    |
 *    ↓
 * IntentRecognizer
 *    |
 *    ├── Consultar gestiones
 *    |
 *    ├── Respuestas controladas
 *    |
 *    ├── Diagnóstico técnico
 *    |
 *    └── IA fallback
 *
 */
class ChatbotService
{


    public function __construct(


        private readonly IntentRecognizerInterface $recognizer,


        private readonly ChatbotResponseBuilder $responseBuilder,


        private readonly GestionStatusService $gestionStatus,


        private readonly AIServiceInterface $aiService,


    ) {}








    /**
     * Procesar mensaje del usuario.
     */
    public function handle(

        string $message,

        ?Authenticatable $user = null

    ): array {


        $userName =
            $user?->nombre
            ??
            config(
                'chatbot.fallback_name',
                'usuario'
            );








        /*
        |--------------------------------------------------------------------------
        | Reconocimiento de intención
        |--------------------------------------------------------------------------
        */


        $intent =
            $this->recognizer->recognize(
                $message
            );










        /*
        |--------------------------------------------------------------------------
        | Consulta de gestiones
        |--------------------------------------------------------------------------
        */


        if(
            $intent->is('consultar_estado')
        ){

            return $this->buildEstadoResponse(

                $user?->id,

                $userName

            );

        }











        /*
        |--------------------------------------------------------------------------
        | Respuestas controladas
        |--------------------------------------------------------------------------
        */


        if(
            !$intent->is('desconocido')
        ){


            return $this->responseBuilder->build(

                $intent,

                $userName,

                $message

            );


        }











        /*
        |--------------------------------------------------------------------------
        | Fallback IA
        |--------------------------------------------------------------------------
        */


        $aiResponse =
            $this->aiService->ask(

                $message,


                [

                    'usuario'=>$userName,


                    'rol'=>
                        $user?->rol?->nombre
                        ??
                        null,


                    'sistemas'=>[

                        'Windows',

                        'Microsoft 365',

                        'Outlook',

                        'Impresoras',

                        'Dell',

                        'VPN',

                        'Redes',

                        'Aplicaciones internas',

                    ],


                ]

            );









        return $this->responseBuilder->build(

            new IntentResult(

                intent:'ai',

                score:0,

                matchedKeywords:[],

                confidence:$aiResponse->confidence

            ),


            $userName,


            $message,


            $aiResponse


        );


    }













    /**
     * Construye respuesta de gestiones.
     */
    private function buildEstadoResponse(

        ?int $userId,

        string $userName

    ): array {


        if(!$userId){


            return [

                'message'=>
                    'Necesitas iniciar sesión para consultar tus gestiones.',


                'quick_actions'=>[],


                'redirect'=>null,


                'items'=>null,


                'intent'=>[

                    'name'=>'consultar_estado',

                    'score'=>0,

                    'confidence'=>1,

                ],

            ];

        }








        $items =
            $this->gestionStatus->getRecentFor(

                $userId

            );









        if(empty($items)){


            return [


                'message'=>

                    "No encontré gestiones registradas a tu nombre, {$userName}. "
                    ."Puedes crear una incidencia o solicitud.",



                'quick_actions'=>[


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


                ],



                'redirect'=>null,


                'items'=>null,


                'intent'=>[

                    'name'=>'consultar_estado',

                    'score'=>1,

                    'confidence'=>1,

                ],


            ];


        }









        return [


            'message'=>

                "Estas son tus gestiones recientes, {$userName}:",



            'quick_actions'=>[


                [

                    'label'=>'Consultar nuevamente',

                    'action'=>'send',

                    'value'=>'consultar estado'

                ],


            ],



            'redirect'=>null,


            'items'=>$items,


            'intent'=>[

                'name'=>'consultar_estado',

                'score'=>1,

                'confidence'=>1,

            ],


        ];



    }



}