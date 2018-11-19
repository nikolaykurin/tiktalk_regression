<?php namespace Progforce\Report\Classes;

use Config;
use Progforce\General\Models\LanguageConfiguration;
use Maatwebsite\Excel\Facades\Excel;
use Progforce\General\Models\LocationWithinWordAux;
use Progforce\General\Models\Word;
use Progforce\General\Models\WordHeIl;

class ReportsHelper
{
    private static function getModel($langId) {
        return $model = ($langId == 1) ? Word::class : WordHeIl::class;
    }

    public static function getCountSoundAndPosition() {
        $lang = request('lang');
        $model = self::getModel($lang);
        $table =  with(new $model)->getTable();

        $rows = $model::from($table . ' as w')->
            select('w.sound_id', 's.sound', 'location_within_word_id')->
            selectRaw('count(w.id) as count')->
            leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'w.sound_id')->
            where('s.sound', '!=', '')->
            groupBy('w.sound_id', 's.sound', 'location_within_word_id')->
            orderBy('s.sound', 'asc')->
            orderBy('location_within_word_id')->
            get();

        $sound = [];
        $soundCount = [];
        foreach ($rows as $row) {
            @$soundCount[$row->sound][$row->location_within_word_id] = $row->count;
            $sounds[$row->sound_id] = $row->sound;
        }
        $locations = LocationWithinWordAux::all();

        $report = [];
        $reportRow = [];
        foreach ($sounds as $sound_id => $sound) {
            $total = 0;
            $reportRow = compact('sound_id', 'sound', 'total');
            foreach ($locations as $location) {
                $count = array_get($soundCount, $sound . '.' . $location->id, 0);
                $reportRow[$location->description] = $count;
                $total += $count;
            }
            $reportRow['total'] = $total;
            $report[] = $reportRow;
        }

        $langCode = LanguageConfiguration::getLangCode($lang);
        $file = 'report_count-sounds'.'_'.$langCode.'_'.date('Y-m-d');
        self::downloadReport($file, $report);
    }

    public static function getWordsReport() {
        $langId = request('langId');
        $reportType = request('type');
        $model = self::getModel($langId);
        $words = $model::
            select('word_id', 'word')->
            groupBy('word_id', 'word')->
            get();
        $report = [];
        foreach ($words as $word) {
            $audioPath = $word->getAudioPath();
            switch ($reportType) {
                case 'with-images':
                    if ($word->hasAllImages()) {
                        $report[] = ['wordId' => $word->word_id, 'word' => $word->word];
                    }
                    break;
                case 'without-images':
                    $hasNotFound = false;
                    $suffixes = Config::get('tiktalk.image_suffixes', []);
                    $row = ['wordId' => $word->word_id, 'word' => $word->word];
                    foreach ($suffixes  as $suffix) {
                        $has =  $word->hasImage($suffix);
                        $row[$word->word_id . '_' . $suffix] = $has ? '' : 'NO';
                        if (!$has) { $hasNotFound = true; }
                    }
                    if ($hasNotFound) {$report[] = $row;}
                    break;
                case 'with-audio':
                    if ($audioPath) {
                        $report[] = ['wordId' => $word->word_id, 'word' => $word->word];
                    }
                    break;
                case 'without-audio':
                    if (!$audioPath) {
                        $report[] = ['wordId' => $word->word_id, 'word' => $word->word];
                    }
                    break;
            }
        }
        $langCode = LanguageConfiguration::getLangCode($langId);
        $file = 'report_words-'.$reportType.'_'.$langCode.'_'.date('Y-m-d');
        self::downloadReport($file, $report, 'Words-'.$reportType);
    }

    private static function downloadReport($file, $list, $sheetName = 'Sheet1') {
        Excel::create($file, function($excel) use ($list, $sheetName) {
            $excel->sheet($sheetName, function ($sheet) use ($list) {
                $sheet->fromArray($list);
            });
        })->download('xlsx');
    }
}