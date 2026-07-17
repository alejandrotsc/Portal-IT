<?php

namespace App\Services\Chatbot;

class KeywordIntentRecognizer implements IntentRecognizerInterface
{
    private array $priorityIntents = [
        'incidencia',
        'solicitud',
        'pase_menor_24h',
        'autorizacion_memorando',
        'consultar_estado',
        'cierre',
        'saludo',
    ];

    public function recognize(string $message): IntentResult
    {
        $normalized = $this->normalize($message);
        $keywords = config('chatbot.keywords', []);

        if(!is_array($keywords) || empty($keywords)){
            return IntentResult::unknown();
        }

        $results = [];

        foreach($keywords as $intent=>$words){
            if(!is_array($words)){
                continue;
            }

            $score = 0;
            $matched = [];

            foreach($words as $keyword){
                if(!is_string($keyword) || trim($keyword) === ''){
                    continue;
                }

                $keywordNormalized = $this->normalize($keyword);

                if($this->matches($normalized, $keywordNormalized)){
                    $score += $this->calculateKeywordWeight($keywordNormalized);
                    $matched[] = $keyword;
                }
            }

            if($score > 0){
                $results[] = [
                    'intent'=>$intent,
                    'score'=>$score,
                    'matched'=>$matched,
                ];
            }
        }

        if(empty($results)){
            return IntentResult::unknown();
        }

        usort($results, function(array $a,array $b): int{
            if($a['score'] === $b['score']){
                return $this->priority($a['intent'])
                    <=>
                    $this->priority($b['intent']);
            }

            return $b['score'] <=> $a['score'];
        });

        $best = $results[0];
        $minimumScore = (int) config('chatbot.min_score',1);

        if($best['score'] < $minimumScore){
            return IntentResult::unknown();
        }

        $alternatives = [];

        foreach(array_slice($results,1,3) as $item){
            $alternatives[] = [
                'intent'=>$item['intent'],
                'score'=>$item['score'],
            ];
        }

        return new IntentResult(
            intent:$best['intent'],
            score:$best['score'],
            matchedKeywords:$best['matched'],
            confidence:$this->calculateConfidence($best['score']),
            alternatives:$alternatives
        );
    }

    private function matches(string $message,string $keyword): bool
    {
        if(str_contains($keyword,' ')){
            return str_contains($message,$keyword);
        }

        return preg_match(
            '/(?:^|\s)'.preg_quote($keyword,'/').'(?:$|\s)/u',
            $message
        ) === 1;
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

        $text = preg_replace('/[^a-z0-9\s]/u',' ',$text) ?? $text;
        $text = preg_replace('/\s+/u',' ',$text) ?? $text;

        return trim($text);
    }

    private function calculateKeywordWeight(string $keyword): int
    {
        $words = count(
            preg_split('/\s+/u',trim($keyword)) ?: []
        );

        return match(true){
            $words >= 4=>5,
            $words === 3=>3,
            $words === 2=>2,
            default=>1,
        };
    }

    private function calculateConfidence(int $score): float
    {
        return match(true){
            $score >= 8=>0.95,
            $score >= 5=>0.90,
            $score >= 3=>0.80,
            $score >= 2=>0.70,
            default=>0.60,
        };
    }

    private function priority(string $intent): int
    {
        $index = array_search(
            $intent,
            $this->priorityIntents,
            true
        );

        return $index === false ? 999 : $index;
    }
}