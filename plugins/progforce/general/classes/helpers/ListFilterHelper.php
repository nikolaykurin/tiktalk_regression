<?php namespace Progforce\General\Classes\Helpers;

use Progforce\General\Models\Complexity;
use Progforce\General\Models\PartOfSpeechAux;

class ListFilterHelper {

    public static function getScopes($model) {
        $scopes = [
   //         'sound' => [
   //             'type' => 'group',
   //             'label' => 'Sound',
  //              'conditions' => 'sound in (:filtered)',
  //              'options' => self::getOptions($model, 'sound')
  //          ],
            'word' => [
                'type' => 'group',
                'label' => 'Word',
                'conditions' => 'word in (:filtered)',
                'options' => self::getOptions($model, 'word')
            ],
            'word_id' => [
                'type' => 'group',
                'label' => 'Word ID',
                'conditions' => 'word_id in (:filtered)',
                'options' => self::getOptions($model, 'word_id')
            ],
            'number_of_syllables' => [
                'type' => 'numberrange',
                'label' => 'Number of Syllables',
                'conditions' => 'number_of_syllables >= \':min\' and number_of_syllables <= \':max\''
            ],
            'intonation' => [
                'type' => 'group',
                'label' => 'Intonation',
                'conditions' => 'intonation in (:filtered)',
                'options' => self::getOptions($model, 'intonation')
            ],
            'location_within_word' => [
                'type' => 'group',
                'label' => 'Location Within Word',
                'conditions' => 'location_within_word in (:filtered)',
                'options' => self::getOptions($model, 'location_within_word')
            ],
            'segment_location_within_phoneme' => [
                'type' => 'group',
                'label' => 'Segment Location Within Phoneme',
                'conditions' => 'segment_location_within_phoneme in (:filtered)',
                'options' => self::getOptions($model, 'segment_location_within_phoneme')
            ],
            'complexity' => [
                'type' => 'group',
                'label' => 'Complexity',
                'modelClass' => Complexity::class,
                'nameFrom' => 'description',
                'conditions' => 'complexity_id in (:filtered)',
            ],
            'part_of_speech' => [
                'type' => 'group',
                'label' => 'Part Of Speech',
                'modelClass' => PartOfSpeechAux::class,
                'nameFrom' => 'name',
                'conditions' => 'part_of_speech_id in (:filtered)',
            ]
        ];

        foreach ($scopes as $key => $value) {
            if (isset($value['options']) && empty($value['options'])) {
                unset($scopes[$key]);
            }
        }

        return $scopes;
    }

    public static function getOptions($model, $field) {
        $array = $model::select($field)->where($field, '!=', '')->distinct()->pluck($field)->toArray();
        sort($array);

        $options = [];
        foreach ($array as $item) {
            $options[$item] = $item;
        }

        return $options;
    }

}