<?php

namespace App\Services\Chatbot\Diagnostics;

class DiagnosticEngine
{
    /*
    |--------------------------------------------------------------------------
    | Ejecutar diagnóstico
    |--------------------------------------------------------------------------
    |
    | Analiza el mensaje recibido, evalúa los diagnósticos configurados
    | y devuelve la coincidencia con mayor puntuación cuando supera
    | el mínimo requerido.
    |
    */

    public function diagnose(
        string $message
    ): ?array {
        $message = $this->normalize(
            $message
        );

        if($message === ''){
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Obtener configuración de diagnósticos
        |--------------------------------------------------------------------------
        */

        $config = config(
            'chatbot_diagnostics',
            []
        );

        if(!is_array($config)){
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Resolver diagnósticos disponibles
        |--------------------------------------------------------------------------
        |
        | Utiliza la sección específica de diagnósticos cuando existe o
        | extrae automáticamente las entradas compatibles de la configuración.
        |
        */

        $diagnostics =
            isset($config['diagnosticos'])
            &&
            is_array($config['diagnosticos'])
                ? $config['diagnosticos']
                : $this->extractDiagnostics(
                    $config
                );

        if(empty($diagnostics)){
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Puntuación mínima requerida
        |--------------------------------------------------------------------------
        */

        $minimumScore = max(
            1,
            (int) (
                $config['minimum_score']
                ??
                1
            )
        );

        $best = null;

        /*
        |--------------------------------------------------------------------------
        | Evaluar diagnósticos
        |--------------------------------------------------------------------------
        |
        | Recorre cada diagnóstico configurado y conserva únicamente
        | la coincidencia que obtenga la mayor puntuación.
        |
        */

        foreach($diagnostics as $key=>$diagnostic){
            if(!is_array($diagnostic)){
                continue;
            }

            $result = $this->evaluateDiagnostic(
                (string) $key,
                $diagnostic,
                $message
            );

            if(!$result){
                continue;
            }

            if(
                !$best
                ||
                $result['score'] > $best['score']
            ){
                $best = $result;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validar resultado final
        |--------------------------------------------------------------------------
        */

        if(
            !$best
            ||
            $best['score'] < $minimumScore
        ){
            return null;
        }

        return $best;
    }

    /*
    |--------------------------------------------------------------------------
    | Evaluar diagnóstico
    |--------------------------------------------------------------------------
    |
    | Comprueba las palabras clave asociadas a un diagnóstico, acumula
    | su puntuación y prepara los pasos y datos que serán devueltos.
    |
    */

    private function evaluateDiagnostic(
        string $key,
        array $diagnostic,
        string $message
    ): ?array {
        $keywords = $diagnostic['keywords'] ?? [];

        if(
            !is_array($keywords)
            ||
            empty($keywords)
        ){
            return null;
        }

        $score = 0;
        $matched = [];

        /*
        |--------------------------------------------------------------------------
        | Evaluar palabras clave
        |--------------------------------------------------------------------------
        */

        foreach($keywords as $keyWord=>$value){
            [$keyword, $weight] =
                $this->resolveKeyword(
                    $keyWord,
                    $value
                );

            if($keyword === ''){
                continue;
            }

            $normalizedKeyword =
                $this->normalize(
                    $keyword
                );

            if(
                $normalizedKeyword === ''
                ||
                !$this->matches(
                    $message,
                    $normalizedKeyword
                )
            ){
                continue;
            }

            $score += $weight;
            $matched[] = $keyword;
        }

        if($score === 0){
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Preparar pasos del diagnóstico
        |--------------------------------------------------------------------------
        */

        $steps =
            $diagnostic['steps']
            ??
            $diagnostic['tips']
            ??
            [];

        if(!is_array($steps)){
            $steps = [];
        }

        $steps = array_values(
            array_filter(
                array_map(
                    static fn(mixed $step): string =>
                        trim((string) $step),
                    $steps
                ),
                static fn(string $step): bool =>
                    $step !== ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Resultado del diagnóstico
        |--------------------------------------------------------------------------
        */

        return [
            'key'=>$key,

            'message'=>trim(
                (string) (
                    $diagnostic['message']
                    ??
                    $diagnostic['mensaje']
                    ??
                    ''
                )
            ),

            'steps'=>$steps,

            'score'=>$score,

            'matched'=>array_values(
                array_unique($matched)
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Resolver palabra clave
    |--------------------------------------------------------------------------
    |
    | Normaliza la estructura de cada palabra clave y determina el peso
    | que debe aportar a la puntuación total del diagnóstico.
    |
    */

    private function resolveKeyword(
        int|string $key,
        mixed $value
    ): array {
        if(
            is_string($key)
            &&
            is_numeric($value)
        ){
            return [
                trim($key),
                max(
                    1,
                    (int) $value
                ),
            ];
        }

        if(is_string($value)){
            return [
                trim($value),
                1,
            ];
        }

        return [
            '',
            0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Extraer diagnósticos
    |--------------------------------------------------------------------------
    |
    | Obtiene de la configuración únicamente las entradas que poseen
    | una estructura válida y contienen palabras clave para evaluación.
    |
    */

    private function extractDiagnostics(
        array $config
    ): array {
        return array_filter(
            $config,
            static fn(
                mixed $value,
                int|string $key
            ): bool =>
                is_string($key)
                &&
                is_array($value)
                &&
                isset($value['keywords']),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar coincidencia
    |--------------------------------------------------------------------------
    |
    | Comprueba si una palabra clave aparece dentro del mensaje como
    | una expresión independiente después de su normalización.
    |
    */

    private function matches(
        string $message,
        string $keyword
    ): bool {
        return preg_match(
            '/(?:^|\s)'
            .preg_quote($keyword, '/')
            .'(?:$|\s)/u',
            $message
        ) === 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizar texto
    |--------------------------------------------------------------------------
    |
    | Convierte el texto a minúsculas, elimina acentos, caracteres
    | especiales y espacios repetidos para facilitar las comparaciones.
    |
    */

    private function normalize(
        string $text
    ): string {
        $text = mb_strtolower(
            trim($text)
        );

        $text = strtr(
            $text,
            [
                'á'=>'a',
                'é'=>'e',
                'í'=>'i',
                'ó'=>'o',
                'ú'=>'u',
                'ü'=>'u',
                'ñ'=>'n',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Eliminar caracteres especiales
        |--------------------------------------------------------------------------
        */

        $text =
            preg_replace(
                '/[^a-z0-9\s]/u',
                ' ',
                $text
            )
            ??
            $text;

        /*
        |--------------------------------------------------------------------------
        | Normalizar espacios
        |--------------------------------------------------------------------------
        */

        $text =
            preg_replace(
                '/\s+/u',
                ' ',
                $text
            )
            ??
            $text;

        return trim($text);
    }
}