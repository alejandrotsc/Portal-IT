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
    |
    | Calcula el resumen general de incidencias, solicitudes y memorandos
    | pertenecientes al usuario, clasifica sus estados y devuelve además
    | las gestiones más recientes ordenadas cronológicamente.
    |
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
        | Cuenta todas las gestiones del usuario recuperando únicamente el
        | campo estado para evitar cargar modelos completos innecesariamente.
        |
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
        |
        | Finaliza inmediatamente cuando el usuario no posee registros en
        | ninguno de los módulos considerados por el resumen.
        |
        */

        if ($summary['total'] === 0) {
            return $summary;
        }


        /*
        |--------------------------------------------------------------------------
        | Consultar únicamente gestiones recientes
        |--------------------------------------------------------------------------
        |
        | Cada módulo devuelve como máximo el límite solicitado. Después,
        | todos los resultados se combinan, ordenan y reducen nuevamente
        | para conservar únicamente las gestiones más recientes en general.
        |
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
    | Mantiene disponible el método getRecentFor() para componentes que
    | todavía esperan únicamente la lista de gestiones recientes.
    |
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
    |
    | Recupera las incidencias más recientes del usuario y las transforma
    | a una estructura uniforme utilizada por el chatbot.
    |
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
    |
    | Recupera las solicitudes más recientes del usuario y normaliza sus
    | datos para presentarlas junto con las demás gestiones.
    |
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
    |
    | Recupera los memorandos más recientes solicitados por el usuario,
    | incluyendo su tipo cuando la relación correspondiente está disponible.
    |
    */

    private function getRecentMemorandos(
    int $userId,
    int $limit
): Collection {
    return Memorando::query()
        ->where('solicitante_id', $userId)
        ->with('tipo')
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
    |
    | Agrupa los diferentes estados utilizados por los módulos del portal
    | en las categorías generales de abiertas, en proceso o finalizadas.
    |
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
    |
    | Convierte el estado almacenado a una representación legible para el
    | usuario, reemplazando separadores y aplicando formato de título.
    |
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
    |
    | Obtiene el nombre visual del tipo de memorando cuando la relación
    | está disponible y utiliza un valor genérico como respaldo.
    |
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
                $memorando->tipo?->nombre_visual
                ?? $memorando->tipo?->nombre
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
    |
    | Resuelve la mejor ruta disponible para consultar una incidencia,
    | utilizando rutas alternativas cuando no existe una vista individual.
    |
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
    |
    | Resuelve la ruta adecuada para consultar una solicitud utilizando
    | la vista individual, historial configurado o formulario como respaldo.
    |
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
    |
    | Determina la ruta de consulta apropiada según el tipo de memorando
    | y utiliza rutas generales de respaldo cuando no existe una vista
    | individual específica.
    |
    */

    private function memorandoUrl(
    Memorando $memorando
): string {
    /*
     * Determinar el tipo real del memorando.
     */
    $typeSlug = null;

    try {
        $typeSlug = $memorando->tipo?->slug;
    } catch (Throwable) {
        $typeSlug = null;
    }


    /*
     * Los pases menores y mayores tienen una vista
     * de detalle centralizada.
     */
    if (
        in_array(
            $typeSlug,
            [
                'pase_temporal',
                'autorizacion',
            ],
            true
        )
        &&
        Route::has('memorandos.show-pase')
    ) {
        return route(
            'memorandos.show-pase',
            $memorando
        );
    }


    /*
     * Compatibilidad futura con un detalle general
     * para otros tipos de memorando.
     */
    if (Route::has('memorandos.show')) {
        return route(
            'memorandos.show',
            $memorando
        );
    }


    /*
     * Respaldo para documentos que todavía no tienen
     * una vista individual.
     */
    if (Route::has('memorandos.historico')) {
        return route(
            'memorandos.historico'
        );
    }


    return '#';
}


    /*
    |--------------------------------------------------------------------------
    | Formatear fecha
    |--------------------------------------------------------------------------
    |
    | Convierte una fecha válida al formato utilizado por el chatbot y
    | devuelve null cuando el valor no puede procesarse correctamente.
    |
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
    |
    | Obtiene la marca de tiempo utilizada internamente para ordenar
    | gestiones provenientes de diferentes módulos.
    |
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
    |
    | Garantiza una longitud máxima para los títulos mostrados y utiliza
    | un texto genérico cuando el contenido original se encuentra vacío.
    |
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
    |
    | Convierte el texto a una forma uniforme sin acentos, separadores
    | innecesarios ni espacios repetidos para facilitar comparaciones.
    |
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