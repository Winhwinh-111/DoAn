<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Normalizer;

class NlpTokenizerService
{
    /* CHỨC NĂNG: Gọi Python tách từ NLP, chuẩn hóa Unicode và lọc Stopwords */
    public function tokenizeBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $scriptPath = storage_path('app/scripts/word_tokenize.py');
        $inputJson = json_encode($texts, JSON_UNESCAPED_UNICODE);

        $process = new Process(['python', $scriptPath]);
        $process->setInput($inputJson);
        $process->setTimeout(60);
        $process->run();

        $tokenizedTexts = [];
        if ($process->isSuccessful()) {
            $outputJson = $process->getOutput();
            $tokenizedTexts = json_decode($outputJson, true) ?? [];
        }

        $stopWords = [
            'và', 'của', 'là', 'trong', 'các', 'những', 'cho', 'về', 'với', 'khi', 'được', 'theo', 'như', 'tại', 'thì', 'mà', 'này',
            'and', 'of', 'is', 'in', 'the', 'for', 'about', 'with', 'when', 'by', 'as', 'at', 'to', 'a', 'an'
        ];

        $cleanedCorpus = [];
        foreach ($texts as $id => $originalText) {
            $rawString = $tokenizedTexts[$id] ?? mb_strtolower($originalText, 'UTF-8');
            
            if (class_exists('Normalizer')) {
                $rawString = Normalizer::normalize($rawString, Normalizer::FORM_C);
            }

            $words = preg_split('/\s+/u', $rawString, -1, PREG_SPLIT_NO_EMPTY);
            $cleanedCorpus[$id] = array_values(array_diff($words, $stopWords));
        }

        return $cleanedCorpus;
    }
}