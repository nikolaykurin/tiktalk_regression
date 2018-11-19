<?php namespace Progforce\General\Models;

use Progforce\General\Models\Sound;
use Progforce\General\Models\WordBase;
use October\Rain\Database\Traits\Validation;

class Word extends WordBase
{
    use Validation;

    public $table = 'progforce_general_words';
    public $timestamps = false;

    public $rules = [
        'word' => 'required',
        'word_id' => 'required',
        'language_id' => 'required',
//        'sound' => 'required',
//        'phoneme' => 'required',
        'number_of_syllables' => 'required|numeric|integer',
//        'intonation' => 'required',
//        'location_within_word' => 'required',
//        'location_within_word_id' => 'required',
//        'segment_location_within_phoneme' => 'required',
//        'segment_location_within_phoneme_id' => 'required',
//        'complexity_id' => 'required',
//        'part_of_speech_id' => 'required',
    ];

    public $belongsTo = [
        'language' => LanguageConfiguration::class,
        'sound' => Sound::class,
        'complexity' => Complexity::class,
        'part_of_speech' => PartOfSpeechAux::class,
        'segment_location_within_phoneme' => FunctionWithinPhoneme::class,
        'utterance_type' => UtteranceType::class,
        'location_within_word' => LocationWithinWordAux::class,
    ];

    public function getSoundOptions() {
        $sounds = Sound::where('language_id', 1)->get()->toArray();
        return array_column($sounds, 'sound', 'id');
    }
}
