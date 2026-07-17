<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Services\Chatbot\ChatbotService;
use App\Services\Chatbot\GestionStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ChatbotController extends Controller
{


    public function __construct(

        private readonly ChatbotService $chatbotService,


        private readonly GestionStatusService $gestionStatus

    ) {}









    /**
     * Procesar mensaje enviado al asistente
     */
    public function message(
        Request $request
    ): JsonResponse {


        $validated =
            $request->validate([

                'message'=>[

                    'required',

                    'string',

                    'max:500'

                ]

            ]);






        $user =
            $request->user();






        /*
        |--------------------------------------------------------------------------
        | Procesamiento principal
        |--------------------------------------------------------------------------
        |
        | Toda la lógica vive en ChatbotService:
        |
        | - intención
        | - diagnósticos
        | - módulos
        | - IA
        |
        */


        $response =
            $this->chatbotService->handle(

                $validated['message'],

                $user

            );








        /*
        |--------------------------------------------------------------------------
        | Guardar conversación
        |--------------------------------------------------------------------------
        */


        $this->saveConversation(

            $user?->id,

            $validated['message'],

            $response

        );







        return response()->json(

            $response

        );


    }












    /**
     * Consulta manual de gestiones
     */
    public function estado(
        Request $request
    ): JsonResponse {


        $user =
            $request->user();






        if(!$user){


            return response()->json([


                'message'=>

                    'Necesitas iniciar sesión para consultar tus gestiones.',



                'quick_actions'=>[],


                'redirect'=>null,


                'items'=>null


            ]);

        }







        $items =
            $this->gestionStatus->getRecentFor(

                $user->id

            );








        return response()->json([



            'message'=>

                empty($items)

                ?

                "No encontré gestiones registradas a tu nombre."

                :

                "Estas son tus gestiones recientes:",





            'quick_actions'=>[



                [

                    'label'=>'Consultar nuevamente',

                    'action'=>'send',

                    'value'=>'consultar estado'

                ]


            ],





            'redirect'=>null,



            'items'=>

                $items ?: null



        ]);



    }












    /**
     * Guardar valoración del usuario
     */
    public function feedback(
        Request $request
    ): JsonResponse {


        $validated =
            $request->validate([



                'conversation_id'=>[

                    'required',

                    'integer',

                    'exists:chatbot_conversations,id'

                ],





                'was_helpful'=>[

                    'required',

                    'boolean'

                ]



            ]);









        ChatbotConversation::where(

            'id',

            $validated['conversation_id']

        )
        ->update([


            'es_util'=>

                $validated['was_helpful']


        ]);








        return response()->json([


            'ok'=>true


        ]);



    }












    /**
     * Registrar conversación del usuario
     */
    private function saveConversation(

        ?int $userId,

        string $message,

        array $response

    ): void {



        /*
        |--------------------------------------------------------------------------
        | Usuarios invitados no generan historial
        |--------------------------------------------------------------------------
        */


        if(!$userId){

            return;

        }








        try {



            $intent =
                $response['intent']
                ??
                [];





            $accion = null;





            if(
                isset($response['redirect'])
                &&
                is_array($response['redirect'])
            ){

                $accion =
                    $response['redirect']['url']
                    ??
                    null;

            }








            ChatbotConversation::create([



                'usuario_id'=>$userId,



                'mensaje'=>$message,



                'respuesta'=>

                    $response['message']
                    ??
                    null,





                'intencion_detectada'=>

                    $intent['name']
                    ??
                    null,





                'puntuacion'=>

                    $intent['score']
                    ??
                    null,





                'accion'=>

                    $accion,



            ]);





        }catch(\Throwable $e){



            report($e);



        }



    }


}