<?php

namespace App\Services\Chatbot;

class KeywordIntentRecognizer implements IntentRecognizerInterface
{
    /*
    |--------------------------------------------------------------------------
    | Prioridad de intenciones
    |--------------------------------------------------------------------------
    |
    | Define el orden utilizado para resolver empates cuando dos o más
    | intenciones obtienen la misma puntuación durante el reconocimiento.
    |
    */

    private array $priorityIntents = [
        'consultar_estado',
        'pase_menor_24h',
        'autorizacion_memorando',
        'incidencia',
        'solicitud',
        'cierre',
        'saludo',
    ];

    /*
    |--------------------------------------------------------------------------
    | Intenciones secundarias
    |--------------------------------------------------------------------------
    |
    | Identifica intenciones que deben descartarse cuando el mensaje
    | contiene además una intención principal con mayor relevancia.
    |
    */

    private array $secondaryIntents = [
        'saludo',
        'cierre',
    ];

    /*
    |--------------------------------------------------------------------------
    | Reconocer intención
    |--------------------------------------------------------------------------
    |
    | Normaliza el mensaje, evalúa las palabras clave configuradas,
    | calcula puntuaciones y devuelve la intención con mayor relevancia
    | junto con su confianza, coincidencias y posibles alternativas.
    |
    */

    public function recognize(string $message): IntentResult
    {
        $normalized = $this->normalize($message);

        $keywords = config(
            'chatbot.keywords',
            []
        );

        if(
            $normalized === ''
            ||
            !is_array($keywords)
            ||
            empty($keywords)
        ){
            return IntentResult::unknown();
        }

        $results = [];

        /*
        |--------------------------------------------------------------------------
        | Evaluar intenciones configuradas
        |--------------------------------------------------------------------------
        |
        | Recorre las palabras clave asociadas a cada intención y acumula
        | su puntuación según el peso configurado o calculado automáticamente.
        |
        */

        foreach($keywords as $intent=>$words){
            if(!is_array($words)){
                continue;
            }

            $score = 0;
            $matched = [];

            foreach($words as $key=>$value){
                [$keyword, $customWeight] =
                    $this->resolveKeyword(
                        $key,
                        $value
                    );

                if($keyword === ''){
                    continue;
                }

                $keywordNormalized =
                    $this->normalize(
                        $keyword
                    );

                if(
                    $keywordNormalized === ''
                    ||
                    !$this->matches(
                        $normalized,
                        $keywordNormalized
                    )
                ){
                    continue;
                }

                $score +=
                    $customWeight
                    ??
                    $this->calculateKeywordWeight(
                        $keywordNormalized
                    );

                $matched[] = $keyword;
            }

            if($score > 0){
                $results[] = [
                    'intent'=>(string) $intent,
                    'score'=>$score,
                    'matched'=>array_values(
                        array_unique($matched)
                    ),
                ];
            }
        }

        if(empty($results)){
            return IntentResult::unknown();
        }

        /*
        |--------------------------------------------------------------------------
        | Eliminar intenciones secundarias
        |--------------------------------------------------------------------------
        */

        $results =
            $this->removeSecondaryIntents(
                $results
            );

        /*
        |--------------------------------------------------------------------------
        | Ordenar resultados
        |--------------------------------------------------------------------------
        |
        | Ordena primero por puntuación y utiliza la prioridad configurada
        | para resolver empates entre intenciones con el mismo resultado.
        |
        */

        usort(
            $results,
            function(
                array $a,
                array $b
            ): int {
                if($a['score'] === $b['score']){
                    return $this->priority(
                        $a['intent']
                    )
                    <=>
                    $this->priority(
                        $b['intent']
                    );
                }

                return $b['score']
                    <=>
                    $a['score'];
            }
        );

        $best = $results[0];

        /*
        |--------------------------------------------------------------------------
        | Validar puntuación mínima
        |--------------------------------------------------------------------------
        */

        $minimumScore =
            max(
                1,
                (int) config(
                    'chatbot.min_score',
                    1
                )
            );

        if($best['score'] < $minimumScore){
            return IntentResult::unknown();
        }

        /*
        |--------------------------------------------------------------------------
        | Preparar alternativas
        |--------------------------------------------------------------------------
        |
        | Conserva hasta tres resultados adicionales para disponer de
        | posibles intenciones alternativas detectadas en el mensaje.
        |
        */

        $alternatives = [];

        foreach(
            array_slice(
                $results,
                1,
                3
            )
            as $item
        ){
            $alternatives[] = [
                'intent'=>$item['intent'],
                'score'=>$item['score'],
            ];
        }

        return new IntentResult(
            intent:$best['intent'],
            score:$best['score'],
            matchedKeywords:$best['matched'],
            confidence:$this->calculateConfidence(
                $best['score']
            ),
            alternatives:$alternatives
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolver palabra clave
    |--------------------------------------------------------------------------
    |
    | Interpreta cada entrada configurada y determina su palabra clave
    | junto con un peso personalizado cuando este haya sido definido.
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
                null,
            ];
        }

        return [
            '',
            null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar intenciones secundarias
    |--------------------------------------------------------------------------
    |
    | Descarta saludos y cierres cuando existe al menos una intención
    | principal, evitando que expresiones sociales oculten la necesidad
    | real identificada dentro del mensaje.
    |
    */

    private function removeSecondaryIntents(
        array $results
    ): array {
        $hasPrimaryIntent =
            collect($results)->contains(
                fn(array $result): bool =>
                    !in_array(
                        $result['intent'],
                        $this->secondaryIntents,
                        true
                    )
            );

        if(!$hasPrimaryIntent){
            return $results;
        }

        return array_values(
            array_filter(
                $results,
                fn(array $result): bool =>
                    !in_array(
                        $result['intent'],
                        $this->secondaryIntents,
                        true
                    )
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar coincidencia
    |--------------------------------------------------------------------------
    |
    | Comprueba que la palabra o expresión aparezca como una coincidencia
    | independiente dentro del mensaje previamente normalizado.
    |
    */

    private function matches(
        string $message,
        string $keyword
    ): bool {
        if(str_contains($keyword, ' ')){
            return preg_match(
                '/(?:^|\s)'
                .preg_quote($keyword, '/')
                .'(?:$|\s)/u',
                $message
            ) === 1;
        }

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
    | Convierte el contenido a minúsculas, elimina acentos y caracteres
    | especiales, y normaliza espacios para facilitar las comparaciones.
    |
    */

    private function normalize(
        string $text
    ): string {
        $text =
            mb_strtolower(
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

        $text =
            preg_replace(
                '/[^a-z0-9\s]/u',
                ' ',
                $text
            )
            ??
            $text;

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

    /*
    |--------------------------------------------------------------------------
    | Calcular peso de palabra clave
    |--------------------------------------------------------------------------
    |
    | Asigna una puntuación automática según la cantidad de palabras que
    | componen la expresión, dando mayor peso a frases más específicas.
    |
    */

    private function calculateKeywordWeight(
        string $keyword
    ): int {
        $words =
            preg_split(
                '/\s+/u',
                trim($keyword)
            )
            ?:
            [];

        return match(true){
            count($words) >= 4=>5,
            count($words) === 3=>3,
            count($words) === 2=>2,
            default=>1,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Calcular nivel de confianza
    |--------------------------------------------------------------------------
    |
    | Convierte la puntuación obtenida en un valor de confianza utilizado
    | posteriormente por otros componentes del chatbot.
    |
    */

    private function calculateConfidence(
        int $score
    ): float {
        return match(true){
            $score >= 8=>0.95,
            $score >= 5=>0.90,
            $score >= 3=>0.80,
            $score >= 2=>0.70,
            default=>0.60,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Resolver prioridad
    |--------------------------------------------------------------------------
    |
    | Obtiene la posición de una intención dentro del orden de prioridad
    | y utiliza un valor elevado cuando no existe una prioridad definida.
    |
    */

    private function priority(
        string $intent
    ): int {
        $index =
            array_search(
                $intent,
                $this->priorityIntents,
                true
            );

        return $index === false
            ? 999
            : $index;
    }
}