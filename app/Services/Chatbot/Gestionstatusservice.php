<?php

namespace App\Services\Chatbot;

use App\Models\Incidencia;
use App\Models\Memorando;
use App\Models\Solicitud;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Throwable;

class GestionStatusService
{
    /*
    |--------------------------------------------------------------------------
    | Obtener resumen y gestiones recientes
    |--------------------------------------------------------------------------
    */

    public function getSummaryFor(
        int $userId,
        int $limit = 5
    ): array {
        $limit = max(
            1,
            min($limit, 10)
        );


        /*
        |--------------------------------------------------------------------------
        | Resumen general
        |--------------------------------------------------------------------------
        |
        | Se cuentan todas las gestiones del usuario, pero solo se selecciona
        | el campo estado. No se cargan modelos completos innecesariamente.
        |--------------------------------------------------------------------------
        */

        $incidenciaStates = Incidencia::query()
            ->where('usuario_id', $userId)
            ->pluck('estado');

        $solicitudStates = Solicitud::query()
            ->where('usuario_id', $userId)
            ->pluck('estado');

        $memorandoStates = Memorando::query()
            ->where('solicitante_id', $userId)
            ->pluck('estado');

        $allStates = $incidenciaStates
            ->concat($solicitudStates)
            ->concat($memorandoStates);

        $summary = [
            'total' => $allStates->count(),
            'abiertas' => 0,
            'en_proceso' => 0,
            'finalizadas' => 0,
            'items' => [],
        ];

        foreach ($allStates as $state) {
            $classification = $this->classifyState(
                (string) $state
            );

            $summary[$classification]++;
        }


        /*
        |--------------------------------------------------------------------------
        | No existen gestiones
        |--------------------------------------------------------------------------
        */

        if ($summary['total'] === 0) {
            return $summary;
        }


        /*
        |--------------------------------------------------------------------------
        | Consultar únicamente gestiones recientes
        |--------------------------------------------------------------------------
        |
        | Cada módulo devuelve como máximo $limit registros.
        | Después se combinan y se toman las últimas $limit en general.
        |--------------------------------------------------------------------------
        */

        $items = collect()
            ->concat(
                $this->getRecentIncidencias(
                    $userId,
                    $limit
                )
            )
            ->concat(
                $this->getRecentSolicitudes(
                    $userId,
                    $limit
                )
            )
            ->concat(
                $this->getRecentMemorandos(
                    $userId,
                    $limit
                )
            )
            ->sortByDesc(
                static fn (array $item): int =>
                    (int) (
                        $item['timestamp']
                        ?? 0
                    )
            )
            ->take($limit)
            ->values()
            ->map(
                static function (array $item): array {
                    /*
                     * timestamp solo se utiliza para ordenar.
                     * No es necesario enviarlo al navegador.
                     */
                    unset($item['timestamp']);

                    return $item;
                }
            )
            ->all();

        $summary['items'] = $items;

        return $summary;
    }


    /*
    |--------------------------------------------------------------------------
    | Compatibilidad con el método anterior
    |--------------------------------------------------------------------------
    |
    | Si otro archivo todavía utiliza getRecentFor(), seguirá funcionando.
    |--------------------------------------------------------------------------
    */

    public function getRecentFor(
        int $userId,
        int $limit = 5
    ): array {
        return $this->getSummaryFor(
            $userId,
            $limit
        )['items'];
    }


    /*
    |--------------------------------------------------------------------------
    | Incidencias recientes
    |--------------------------------------------------------------------------
    */

    private function getRecentIncidencias(
        int $userId,
        int $limit
    ): Collection {
        return Incidencia::query()
            ->where('usuario_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(
                function (Incidencia $incidencia): array {
                    $title = trim(
                        (string) (
                            $incidencia->titulo
                            ?? $incidencia->descripcion
                            ?? 'Incidencia registrada'
                        )
                    );

                    return [
                        'id' => 'incidencia-'.$incidencia->getKey(),

                        'tipo' => 'Incidencia',

                        'codigo' =>
                            $incidencia->codigo
                            ?? null,

                        /*
                         * El Blade actual utiliza item.status.
                         */
                        'status' => $this->formatState(
                            (string) $incidencia->estado
                        ),

                        /*
                         * Se mantiene estado por compatibilidad con otros usos.
                         */
                        'estado' => $this->formatState(
                            (string) $incidencia->estado
                        ),

                        'title' => $this->limitText(
                            $title,
                            100
                        ),

                        'url' => $this->incidenciaUrl(
                            $incidencia
                        ),

                        'fecha' => $this->formatDate(
                            $incidencia->created_at
                        ),

                        'timestamp' => $this->timestamp(
                            $incidencia->created_at
                        ),
                    ];
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Solicitudes recientes
    |--------------------------------------------------------------------------
    */

    private function getRecentSolicitudes(
        int $userId,
        int $limit
    ): Collection {
        return Solicitud::query()
            ->where('usuario_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(
                function (Solicitud $solicitud): array {
                    $title = trim(
                        (string) (
                            $solicitud->asunto
                            ?? $solicitud->descripcion
                            ?? 'Solicitud registrada'
                        )
                    );

                    return [
                        'id' => 'solicitud-'.$solicitud->getKey(),

                        'tipo' => 'Solicitud',

                        'codigo' =>
                            $solicitud->folio
                            ?? $solicitud->codigo
                            ?? null,

                        'status' => $this->formatState(
                            (string) (
                                $solicitud->estado
                                ?? 'Registrada'
                            )
                        ),

                        'estado' => $this->formatState(
                            (string) (
                                $solicitud->estado
                                ?? 'Registrada'
                            )
                        ),

                        'title' => $this->limitText(
                            $title,
                            100
                        ),

                        'url' => $this->solicitudUrl(
                            $solicitud
                        ),

                        'fecha' => $this->formatDate(
                            $solicitud->created_at
                        ),

                        'timestamp' => $this->timestamp(
                            $solicitud->created_at
                        ),
                    ];
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Memorandos recientes
    |--------------------------------------------------------------------------
    */

    private function getRecentMemorandos(
        int $userId,
        int $limit
    ): Collection {
        return Memorando::query()
            ->where('solicitante_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(
                function (Memorando $memorando): array {
                    $title = trim(
                        (string) (
                            $memorando->asunto
                            ?? $memorando->codigo
                            ?? 'Memorando registrado'
                        )
                    );

                    return [
                        'id' => 'memorando-'.$memorando->getKey(),

                        'tipo' => $this->memorandoType(
                            $memorando
                        ),

                        'codigo' =>
                            $memorando->codigo
                            ?? null,

                        'status' => $this->formatState(
                            (string) $memorando->estado
                        ),

                        'estado' => $this->formatState(
                            (string) $memorando->estado
                        ),

                        'title' => $this->limitText(
                            $title,
                            100
                        ),

                        'url' => $this->memorandoUrl(
                            $memorando
                        ),

                        'fecha' => $this->formatDate(
                            $memorando->created_at
                        ),

                        'timestamp' => $this->timestamp(
                            $memorando->created_at
                        ),
                    ];
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Clasificar estado
    |--------------------------------------------------------------------------
    */

    private function classifyState(
        string $state
    ): string {
        $state = $this->normalizeText(
            $state
        );

        /*
        |--------------------------------------------------------------------------
        | En proceso
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($state, 'proceso')
            || str_contains($state, 'revision')
            || str_contains($state, 'en firma')
            || str_contains($state, 'en_firma')
            || str_contains($state, 'gestion')
            || str_contains($state, 'tramite')
        ) {
            return 'en_proceso';
        }


        /*
        |--------------------------------------------------------------------------
        | Abiertas o pendientes
        |--------------------------------------------------------------------------
        */

        if (
            $state === ''
            || str_contains($state, 'abierta')
            || str_contains($state, 'abierto')
            || str_contains($state, 'nuevo')
            || str_contains($state, 'nueva')
            || str_contains($state, 'pendiente')
            || str_contains($state, 'registrada')
            || str_contains($state, 'registrado')
            || str_contains($state, 'generado')
        ) {
            return 'abiertas';
        }


        /*
        |--------------------------------------------------------------------------
        | Finalizadas
        |--------------------------------------------------------------------------
        */

        return 'finalizadas';
    }


    /*
    |--------------------------------------------------------------------------
    | Formatear estado
    |--------------------------------------------------------------------------
    */

    private function formatState(
        string $state
    ): string {
        $state = trim($state);

        if ($state === '') {
            return 'Registrada';
        }

        return str($state)
            ->replace('_', ' ')
            ->lower()
            ->title()
            ->toString();
    }


    /*
    |--------------------------------------------------------------------------
    | Tipo de memorando
    |--------------------------------------------------------------------------
    */

    private function memorandoType(
        Memorando $memorando
    ): string {
        /*
         * Si el modelo tiene cargada la relación tipo,
         * se utiliza su nombre.
         */
        try {
            $typeName =
                $memorando->tipo?->nombre
                ?? $memorando->tipo?->label
                ?? null;

            if (
                is_string($typeName)
                && trim($typeName) !== ''
            ) {
                return trim($typeName);
            }
        } catch (Throwable) {
            /*
             * La relación tipo podría no existir en alguna versión
             * del modelo. Se utiliza el texto genérico.
             */
        }

        return 'Memorando';
    }


    /*
    |--------------------------------------------------------------------------
    | URL de incidencia
    |--------------------------------------------------------------------------
    */

    private function incidenciaUrl(
        Incidencia $incidencia
    ): string {
        if (Route::has('incidencias.show')) {
            return route(
                'incidencias.show',
                $incidencia
            );
        }

        if (Route::has('mis-incidencias')) {
            return route(
                'mis-incidencias'
            );
        }

        if (Route::has('incidencias.index')) {
            return route(
                'incidencias.index'
            );
        }

        return '#';
    }


    /*
    |--------------------------------------------------------------------------
    | URL de solicitud
    |--------------------------------------------------------------------------
    */

    private function solicitudUrl(
        Solicitud $solicitud
    ): string {
        if (Route::has('solicitudes.show')) {
            return route(
                'solicitudes.show',
                $solicitud
            );
        }

        $routeName = config(
            'chatbot.modules.solicitud.index'
        );

        if (
            is_string($routeName)
            && Route::has($routeName)
        ) {
            return route($routeName);
        }

        if (Route::has('solicitudes.create')) {
            return route(
                'solicitudes.create'
            );
        }

        return '#';
    }


    /*
    |--------------------------------------------------------------------------
    | URL de memorando
    |--------------------------------------------------------------------------
    */

    private function memorandoUrl(
        Memorando $memorando
    ): string {
        if (Route::has('memorandos.show')) {
            return route(
                'memorandos.show',
                $memorando
            );
        }

        if (Route::has('memorandos.historico')) {
            return route(
                'memorandos.historico'
            );
        }

        return '#';
    }


    /*
    |--------------------------------------------------------------------------
    | Fecha
    |--------------------------------------------------------------------------
    */

    private function formatDate(
        mixed $date
    ): ?string {
        if (!$date) {
            return null;
        }

        try {
            return $date->format(
                'd/m/Y H:i'
            );
        } catch (Throwable) {
            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Timestamp para ordenar
    |--------------------------------------------------------------------------
    */

    private function timestamp(
        mixed $date
    ): int {
        if (!$date) {
            return 0;
        }

        try {
            return (int) $date->getTimestamp();
        } catch (Throwable) {
            return 0;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Limitar texto
    |--------------------------------------------------------------------------
    */

    private function limitText(
        string $text,
        int $length
    ): string {
        $text = trim($text);

        if ($text === '') {
            return 'Gestión registrada';
        }

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr(
            $text,
            0,
            $length - 3
        ).'...';
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar texto
    |--------------------------------------------------------------------------
    */

    private function normalizeText(
        string $text
    ): string {
        return str($text)
            ->replace('_', ' ')
            ->lower()
            ->ascii()
            ->squish()
            ->toString();
    }
}