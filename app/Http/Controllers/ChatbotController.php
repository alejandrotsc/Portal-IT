<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Services\Chatbot\ChatbotResponseBuilder;
use App\Services\Chatbot\GestionStatusService;
use App\Services\Chatbot\IntentRecognizerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{

    public function __construct(

        private readonly IntentRecognizerInterface $recognizer,

        private readonly ChatbotResponseBuilder $responseBuilder,

        private readonly GestionStatusService $gestionStatus,

    ) {}





    /**
     * Procesar mensaje enviado por el usuario
     */
    public function message(Request $request): JsonResponse
    {

        $validated = $request->validate([

            'message' => [
                'required',
                'string',
                'max:500'
            ]

        ]);



        $usuario = $request->user();



        $nombreUsuario = $usuario?->nombre
            ??
            config('chatbot.fallback_name');



        /*
        |--------------------------------------------------------------------------
        | Detectar intención
        |--------------------------------------------------------------------------
        */

        $intent = $this->recognizer->recognize(
            $validated['message']
        );





        /*
        |--------------------------------------------------------------------------
        | Construcción de respuesta
        |--------------------------------------------------------------------------
        */

        if ($intent->is('consultar_estado')) {


            $payload = $this->buildEstadoResponse(

                $usuario?->id,

                $nombreUsuario

            );


            $accion = 'consulta_gestiones';



        } else {


            $payload = $this->responseBuilder->build(

                $intent,

                $nombreUsuario

            );


            $accion = $this->resolveAction(

                $intent->intent

            );


        }





        /*
        |--------------------------------------------------------------------------
        | Guardar conversación
        |--------------------------------------------------------------------------
        */

        $this->saveConversation(

            $usuario?->id,

            $validated['message'],

            $intent,

            $payload['message'],

            $accion

        );





        return response()->json($payload);

    }









    /**
     * Consulta directa de gestiones
     */
    public function estado(Request $request): JsonResponse
    {

        $usuario = $request->user();



        return response()->json(

            $this->buildEstadoResponse(

                $usuario?->id,

                $usuario?->nombre
                    ??
                    config('chatbot.fallback_name')

            )

        );

    }









    /**
     * Guardar feedback 👍 👎
     */
    public function feedback(Request $request): JsonResponse
    {

        $validated = $request->validate([

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

        )->update([

            'es_util'=>$validated['was_helpful']

        ]);




        return response()->json([

            'ok'=>true

        ]);

    }









    /**
     * Respuesta con gestiones del usuario
     */
    private function buildEstadoResponse(

        ?int $usuarioId,

        string $nombreUsuario

    ): array {


        if(!$usuarioId){


            return [

                'message'=>
                    'Necesitas iniciar sesión para consultar tus gestiones.',


                'quick_actions'=>[],


                'redirect'=>null,


                'items'=>null

            ];

        }





        $items = $this->gestionStatus->getRecentFor(

            $usuarioId

        );






        if(empty($items)){


            return [

                'message'=>

                    "No encontré gestiones registradas a tu nombre, {$nombreUsuario}. "
                    ."Puedes crear una incidencia, solicitud, pase o autorización.",



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

                    ]



                ],



                'redirect'=>null,


                'items'=>null


            ];

        }






        return [

            'message'=>

                "Estas son tus gestiones recientes, {$nombreUsuario}:",



            'quick_actions'=>[


                [

                    'label'=>'Consultar nuevamente',

                    'action'=>'send',

                    'value'=>'consultar estado'

                ]

            ],



            'redirect'=>null,



            'items'=>$items


        ];

    }









    /**
     * Determina acción sugerida
     */
    private function resolveAction(string $intent): ?string
    {

        return match($intent){


            'incidencia'=>
                'incidencia_create',



            'solicitud'=>
                'solicitud_create',



            'pase_menor_24h'=>
                'pase_menor_create',



            'autorizacion_memorando'=>
                'memorando_create',



            default=>
                null


        };

    }









    /**
     * Registrar historial del chatbot
     */
    private function saveConversation(

        ?int $usuarioId,

        string $mensaje,

        $intent,

        string $respuesta,

        ?string $accion

    ): void {



        if(!$usuarioId){

            return;

        }




        try {


            ChatbotConversation::create([


                'usuario_id'=>$usuarioId,


                'mensaje'=>$mensaje,


                'intencion_detectada'=>$intent->intent,


                'puntuacion'=>$intent->score,


                'respuesta'=>$respuesta,


                'accion'=>$accion,


            ]);



        }catch(\Throwable $e){


            report($e);


        }


    }


}