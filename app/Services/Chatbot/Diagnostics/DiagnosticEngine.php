<?php

namespace App\Services\Chatbot\Diagnostics;

class DiagnosticEngine
{
    public function diagnose(
        string $message
    ): ?array {
        $message = $this->normalize(
            $message
        );

        if($message === ''){
            return null;
        }

        $config = config(
            'chatbot_diagnostics',
            []
        );

        if(!is_array($config)){
            return null;
        }

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

        $minimumScore = max(
            1,
            (int) (
                $config['minimum_score']
                ??
                1
            )
        );

        $best = null;

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

        if(
            !$best
            ||
            $best['score'] < $minimumScore
        ){
            return null;
        }

        return $best;
    }

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
}