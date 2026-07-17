<?php

namespace App\Services\Chatbot\Diagnostics;

class DiagnosticEngine
{
    public function diagnose(string $message): ?array
    {
        $message = $this->normalize($message);

        if($message === ''){
            return null;
        }

        $diagnostics = config(
            'chatbot_diagnostics.diagnosticos',
            config('chatbot_diagnostics',[])
        );

        if(!is_array($diagnostics) || empty($diagnostics)){
            return null;
        }

        $minimumScore = (int) config(
            'chatbot_diagnostics.minimum_score',
            1
        );

        $best = null;
        $bestScore = 0;

        foreach($diagnostics as $key=>$diagnostic){
            if(!is_array($diagnostic)){
                continue;
            }

            $keywords = $diagnostic['keywords'] ?? [];

            if(!is_array($keywords)){
                continue;
            }

            $score = 0;
            $matched = [];

            foreach($keywords as $keyword=>$weight){
                /*
                |--------------------------------------------------------------------------
                | Compatibilidad con ambos formatos
                |--------------------------------------------------------------------------
                |
                | Asociativo:
                | 'no enciende'=>5
                |
                | Simple:
                | 'no enciende'
                |
                */

                if(is_int($keyword)){
                    $keywordText = $weight;
                    $keywordWeight = 1;
                }else{
                    $keywordText = $keyword;
                    $keywordWeight = is_numeric($weight)
                        ? (int) $weight
                        : 1;
                }

                if(!is_string($keywordText) || trim($keywordText) === ''){
                    continue;
                }

                $keywordNormalized = $this->normalize($keywordText);

                if(
                    $keywordNormalized !== ''
                    &&
                    str_contains($message,$keywordNormalized)
                ){
                    $score += max(1,$keywordWeight);
                    $matched[] = $keywordText;
                }
            }

            if($score > $bestScore){
                $bestScore = $score;

                $best = [
                    'key'=>(string) $key,
                    'message'=>$diagnostic['message']
                        ?? $diagnostic['mensaje']
                        ?? '',
                    'steps'=>$diagnostic['steps']
                        ?? $diagnostic['tips']
                        ?? [],
                    'score'=>$score,
                    'matched'=>$matched,
                ];
            }
        }

        if(!$best || $bestScore < $minimumScore){
            return null;
        }

        return $best;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));

        $text = strtr($text,[
            'á'=>'a',
            'é'=>'e',
            'í'=>'i',
            'ó'=>'o',
            'ú'=>'u',
            'ü'=>'u',
            'ñ'=>'n',
        ]);

        $text = preg_replace(
            '/[^a-z0-9\s]/u',
            ' ',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\s+/u',
            ' ',
            $text
        ) ?? $text;

        return trim($text);
    }
}