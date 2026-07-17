<?php

namespace App\Services\Chatbot;

use App\Models\Incidencia;
use App\Models\Solicitud;
use App\Models\Memorando;
use App\Models\MemorandoTipo;
use Illuminate\Support\Facades\Route;


/**
 * Consulta las gestiones del usuario autenticado:
 *
 * - Incidencias
 * - Solicitudes
 * - Pases temporales (<24h)
 * - Autorizaciones (>24h)
 *
 * Adaptado al esquema Portal TI TVC.
 */
class GestionStatusService
{

    private const LIMIT_PER_MODULE = 5;



    public function getRecentFor(int $userId): array
    {

        $items = [];


        $items = array_merge(

            $items,

            $this->safeFetch(
                fn () => $this->mapIncidencias($userId)
            ),


            $this->safeFetch(
                fn () => $this->mapSolicitudes($userId)
            ),


            $this->safeFetch(
                fn () => $this->mapMemorandos($userId)
            )

        );



        usort(
            $items,
            fn ($a, $b) =>
                strcmp(
                    $b['date'],
                    $a['date']
                )
        );


        return $items;

    }





    private function safeFetch(callable $callback): array
    {

        try {

            return $callback();

        } catch (\Throwable $e) {


            report($e);


            return [];

        }

    }







    private function mapIncidencias(int $userId): array
    {

        if (!class_exists(Incidencia::class)) {

            return [];

        }



        return Incidencia::query()

            ->where(
                'usuario_id',
                $userId
            )

            ->latest()

            ->limit(self::LIMIT_PER_MODULE)

            ->get()

            ->map(function ($i) {


                return [

                    'tipo' => 'Incidencia',


                    'title' =>
                        $i->titulo
                        ??
                        "Incidencia #{$i->id}",


                    'status' =>
                        $i->estado
                        ??
                        'Sin estado',


                    'date' =>
                        optional($i->created_at)
                        ->toDateTimeString()
                        ??
                        '',


                    'url' =>
                        Route::has('incidencias.show')
                        ?
                        route(
                            'incidencias.show',
                            $i->id
                        )
                        :
                        null,


                ];


            })

            ->toArray();


    }








    private function mapSolicitudes(int $userId): array
    {


        if (!class_exists(Solicitud::class)) {

            return [];

        }



        return Solicitud::query()

            ->where(
                'usuario_id',
                $userId
            )


            ->latest()


            ->limit(self::LIMIT_PER_MODULE)


            ->get()


            ->map(function ($s) {


                return [


                    'tipo' => 'Solicitud',



                    'title' =>
                        $s->asunto
                        ??
                        "Solicitud #{$s->id}",



                    'status' =>
                        $s->estado
                        ??
                        'Sin estado',



                    'date' =>
                        optional($s->created_at)
                        ->toDateTimeString()
                        ??
                        '',



                    'url' => null,


                ];


            })


            ->toArray();



    }









    private function mapMemorandos(int $userId): array
    {


        if (!class_exists(Memorando::class)) {

            return [];

        }



        return Memorando::query()

            ->where(
                'solicitante_id',
                $userId
            )


            ->with('tipo')


            ->latest()


            ->limit(self::LIMIT_PER_MODULE)


            ->get()


            ->map(function ($m) {


                $tipo = 'Memorando';


                if (
                    $m->tipo &&
                    $m->tipo->slug === 'pase_temporal'
                ) {

                    $tipo = 'Pase (<24h)';

                }


                if (
                    $m->tipo &&
                    $m->tipo->slug === 'autorizacion'
                ) {

                    $tipo = 'Autorización (>24h)';

                }



                return [


                    'tipo' => $tipo,



                    'title' =>
                        $m->asunto
                        ??
                        "Memorando #{$m->id}",



                    'status' =>
                        $m->estado
                        ??
                        'Sin estado',



                    'date' =>
                        optional($m->created_at)
                        ->toDateTimeString()
                        ??
                        '',



                    'url' =>
                        Route::has('memorandos.historico')
                        ?
                        route(
                            'memorandos.historico'
                        )
                        :
                        null,


                ];


            })

            ->toArray();


    }


}