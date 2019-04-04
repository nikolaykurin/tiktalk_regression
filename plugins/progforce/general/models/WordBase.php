<?php namespace Progforce\General\Models;

use Model;
use Config;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Classes\Helpers\PatientTreatmentPlanHelper;

class WordBase extends Model
{
    protected $fillable = [
        'language_id',
        'word',
        'word_id',
        'sound',
        'phoneme',
        'number_of_syllables',
        'location_within_word_id',
        'segment_location_within_phoneme_id',
        'complexity_id',
        'part_of_speech_id',
        'transcription1',
    ];

    public function getByPhase($phase = null, Array $sounds = []) {
        $qry = self::from($this->table . ' as w');
        if ($sounds) {
            $qry = $qry->selectRaw('w.word_id, REPLACE(TRIM(w.word), \' \', \'_\') as word, w.transcription1, s.sound');
        } else {
            $qry = $qry->selectRaw('w.word_id, REPLACE(TRIM(w.word), \' \', \'_\') as word, w.transcription1');
        }
 
        if ($phase) {
            if (in_array($phase->id, [PatientTreatmentPlanHelper::$BENCHMARKING_TUNING, PatientTreatmentPlanHelper::$BENCHMARKING ])) {
                 $qry = $qry->join(
                        'progforce_general_prerecorded_words as p',
                        'w.word_id',
                        '=',
                        'p.asset_id');
            } else {
                $this->setPhaseWhere($qry, $phase);
                if ($sounds) {
                    $qry = $qry->
                        leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'w.sound_id')->
                        whereIn('s.sound', array_column($sounds, 'sound'));
                }
            }
        }

        $words =  $qry->where('has_image', true)
            ->where('has_audio', true)
            ->where('transcription1', '!=', '')
            ->orderByRaw('RAND()')
            ->distinct()
            ->get()
            ->toArray();
        $this->setAssetsSizes($words);
        return $words;
    }

    private function setPhaseWhere(&$qry, $phase) {
        $numberOfSyllables = strtolower(trim($phase->number_of_syllables));
        if (preg_match('/([0-9]+)([<=]+)x([<=]+)([0-9]+)/', $numberOfSyllables, $regs)) {
            $lessValue = $regs[1];
            $lessCond = str_replace('<', '>', $regs[2]);
            $moreCond = $regs[3];
            $moreValue = $regs[4];

            $qry = $qry->where('number_of_syllables', $lessCond, $lessValue)->
                where('number_of_syllables', $moreCond, $moreValue);
        }
        if ($phase->part_of_speech != 4) {
            $qry = $qry->where('part_of_speech_id', $phase->part_of_speech);
        }
        if ($phase->function_within_phoneme_id != 4) {
            $qry = $qry->where('segment_location_within_phoneme_id', $phase->function_within_phoneme_id);
        }
        if ($phase->location_within_word != 6) {
            $qry = $qry->where('location_within_word_id', $phase->location_within_word);
        }
        if ($phase->complexity_id != 3 && $phase->utterance_type_id != 2) {
            $qry = $qry->where('complexity_id', $phase->complexity_id);
        }
        if ($phase->utterance_type_id) { 
            $qry = $qry->where('utterance_type_id', $phase->utterance_type_id);

            if ($phase->utterance_type_id == 2) { // sentences
                $soundOccurrences = strtolower(trim($phase->sound_occurrences));
                if (preg_match('/([><=]+)([0-9]+)/', $soundOccurrences, $regs)) {
                    $condition = $regs[1];
                    $value = $regs[2];

                    $qry = $qry->where('sound_occurrences', $condition, $value);
                }
            }
        }
    }

    private function setAssetsSizes(&$words) {
        $langCode = $this->getLangCode();
        foreach ($words as &$word) {
            $imagePath = PathHelper::getWordImagePath($langCode, $word['word_id'], true);
            $audioPath = PathHelper::getWordAudioPath($langCode, $word['word_id'], true);
            $imageSize = $imagePath ? filesize($imagePath) : 0;
            $audioSize = $audioPath ? filesize($audioPath) : 0;
            $word['image_size'] = $imageSize;
            $word['audio_size'] = $audioSize;
        }
    }

    public function getLangCode() {
        $langs = array_column(Config::get('languages'), 'code', 'wordModel');
        return array_get($langs, class_basename($this), null);
    }

    public function getSoundOptions() {
        $langId = $this->user->language_id;
        $sounds = Sound::where('language_id', $langId)->get()->toArray();
        return array_column($sounds, 'sound', 'id');
    }

    public function getImagePath() {
        return PathHelper::getWordImagePath($this->getLangCode(), $this->word_id);
    }

    public function hasImage($suffix = '') {
        $path = PathHelper::getImagesPath($this->getLangCode());
        $fileName = $this->word_id . $suffix . '.png';
        return \Storage::exists($path . '/' . $fileName);
    }

    public function hasAllImages() {
        $suffixes = Config::get('tiktalk.image_suffixes', []);
        foreach ($suffixes as $suffix) {
            if (!$this->hasImage($suffix)) {
                return false;
            }
        }
        return true;
    }
    
    public function getAudioPath() {
        return PathHelper::getWordAudioPath($this->getLangCode(), $this->word_id);
    }

}
