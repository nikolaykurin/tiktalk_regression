<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class TreatmentPhase extends Model
{
    use Validation;

    public $table = 'progforce_general_treatment_phases';

    public $timestamps = false;

    public $rules = [
        'description' => 'required',
        'number_of_syllables' => 'required',
        'intonation' => 'required',
        'location_within_word' => 'required',
        'function_within_phoneme_id' => 'required',
        'complexity_id' => 'required',
        'part_of_speech' => 'required'
    ];

    public $belongsTo = [
        'complexity' => Complexity::class,
        'location_within_word_field' => [
            LocationWithinWordAux::class,
            'key' => 'location_within_word',
        ],
        'game_mode' => GameMode::class,
        'segment_location_within_phoneme_field' => [
            FunctionWithinPhoneme::class,
            'key' => 'function_within_phoneme_id',
        ],
        'part_of_speech_field' => [
            PartOfSpeechAux::class,
            'key' => 'part_of_speech',
        ],
        'utterance_type' => UtteranceType::class,
    ];

}