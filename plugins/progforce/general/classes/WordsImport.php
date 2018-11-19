<?php

namespace Progforce\General\Classes;

use \Maatwebsite\Excel\Facades\Excel;
use Progforce\General\Models\LanguageConfiguration;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Models\Word;
use Progforce\General\Models\WordHeIl;

class WordsImport
{
    public static function getFilePath($fileName) {
        return 'backup/' . $fileName;
    }

    public static function importWords($xlsx, $langCode = 'en-us') {

        $setField = function(&$dbFields, $item, $key, $def = '') {
            $val = trim(array_get($item, $key, $def));
            $val = !$val && $def ? $def : $val;
            $dbFields[$key] = $val ? $val : null;
        };

        $addSound = function($langId, $sound) {
             return \Progforce\General\Models\Sound::getSoundId($langId, $sound);
        };

        $file = 'backup/' . $xlsx;
        $model = LanguageConfiguration::getWordModelByCode($langCode);
        $model::query()->truncate();

        $words = [];
        $langId = LanguageConfiguration::getLangIdByCode($langCode);
        Excel::filter('chunk')->selectSheetsByIndex(0)->load($file)->chunk(100, function($result) use($langCode, &$words, $setField, $addSound ) {
            $rows = [];
            $list = $result->toArray();
            foreach ($list as $item) {
                if (empty($item['word'])) { continue; }

                $wordId = (int)$item['word_id'];
                $langId = LanguageConfiguration::getLangIdByCode($langCode);
                $dbFields = [
                    'language_id' => $langId,
                    'word' => trim($item['word']),
                    'word_id' => $wordId,
                    'has_audio' => !empty(Helpers\PathHelper::getWordAudioPath($langCode, $wordId)),
                    'has_image' => !empty(Helpers\PathHelper::getWordImagePath($langCode, $wordId)),
                ];

                $dbFields['sound_id'] = $addSound($langId, $item['sound']);
                $setField($dbFields, $item, 'sound_occurrences');

                $setField($dbFields, $item, 'phoneme');
                $setField($dbFields, $item, 'transcription1');
                $setField($dbFields, $item, 'number_of_syllables');
                $setField($dbFields, $item, 'intonation');

                $setField($dbFields, $item, 'location_within_word_id');
                $setField($dbFields, $item, 'segment_location_within_phoneme_id');
                $setField($dbFields, $item, 'complexity_id');
                $setField($dbFields, $item, 'utterance_type_id', 1);
                $setField($dbFields, $item, 'part_of_speech_id');

                $rows[] = $dbFields;

                $key = strtolower(trim($item['word']));
                if (!array_key_exists($key, $words)) {
                    $words[$key] = $item['transcription1'];
                }
            }
            $model = LanguageConfiguration::getWordModelByCode($langCode);
            $model::insert($rows);
        }, false);

        AcousticModelHelper::updateDictionaries($langId, $words);
    }

    public static function downloadWordsEn() {
        self::exporWordstToXLS(Word::class, 'words_en-us_'.date('Y-m-d'));
    }

    public static function downloadWordsHe() {
        self::exporWordstToXLS(WordHeIl::class, 'words_he-il_'.date('Y-m-d'));
    }

    private static function exporWordstToXLS($model, $file) {
        $table =  with(new $model)->getTable();
        $words = $model::from($table .' as w')->
                select(
                    'w.id', 'w.word_id', 'w.word', 's.id as sound_id', 's.sound',  
                    'w.sound_occurrences',
                    'w.phoneme', 'w.number_of_syllables', 'w.intonation', 
                    'w.location_within_word_id', 'l.description as location_within_word',
                    'w.segment_location_within_phoneme_id',
                    'sl.function as segment_location_within_phoneme',
                    'w.complexity_id', 'cm.description as complexity',
                    'w.utterance_type_id', 'ut.name as utterance_type',
                    'w.part_of_speech_id', 'ps.name as part_of_speech',
                    'w.transcription1'
                )->
            leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'w.sound_id')->
            leftJoin('progforce_general_location_within_word_aux as l', 'l.id', '=', 'w.location_within_word_id')->
            leftJoin('progforce_general_function_within_phoneme as sl', 'sl.id', '=', 'w.segment_location_within_phoneme_id')->
            leftJoin('progforce_general_complexities as cm', 'cm.id', '=', 'w.complexity_id')->
            leftJoin('progforce_general_part_of_speech_aux as ps', 'ps.id', '=', 'w.part_of_speech_id')->
            leftJoin('progforce_general_utterance_types as ut', 'ut.id', '=', 'w.utterance_type_id')->
            orderBy('w.id')->
            get();
        Excel::create($file, function($excel) use ($words) {
            $excel->sheet('Sheet1', function ($sheet) use ($words) {
                $sheet->fromArray($words->toArray());
            });
        })->download('xlsx');
    }
}
