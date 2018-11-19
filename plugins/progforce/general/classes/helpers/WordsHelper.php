<?php namespace Progforce\General\Classes\Helpers;

use Progforce\General\Models\Word;
use Progforce\General\Models\WordHeIl;
use Exception;

class WordsHelper {

    public static function getWordsListByLang(array $words, $lang = 'en-us') {
        switch ($lang) {
            case 'en-us':
                $model = Word::class;
                break;
            case 'he-il':
                $model = WordHeIl::class;
                break;
            default:
                throw new Exception('Wrong \'lang\' identificator!');
        }

        return $model::whereIn('word_id', $words)->distinct()->get(['word', 'word_id', 'transcription1']);
    }

}