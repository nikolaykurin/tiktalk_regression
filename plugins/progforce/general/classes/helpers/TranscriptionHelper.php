<?php namespace Progforce\General\Classes\Helpers;

use Illuminate\Support\Facades\Log;

class TranscriptionHelper {

    public static $SPACE_DELIMITER = ' ';
    public static $EMPTY_STRING = '';

    public static function normalize($transcription, $lang_code) {
        $phonemesArray = config('phonemes')[$lang_code];

        $phonemes = explode(self::$SPACE_DELIMITER, trim(strtoupper($transcription)));

        foreach ($phonemes as $key=>$phoneme) {
            if (!in_array($phoneme, $phonemesArray)) {
                $newPhoneme = null;
                if (preg_match('/\\d/', $phoneme)) {
                    $newPhoneme = preg_replace('/\\d/', self::$EMPTY_STRING, $phoneme);
                } elseif (strlen($phoneme) === 1) {
                    foreach ($phonemesArray as $item) {
                        if ($item[0] === $phoneme) {
                            $newPhoneme = $item;
                            break;
                        }
                    }
                } else if (strlen($phoneme) >= 2) {
                    $newPhoneme = substr($phoneme, 0, 1);
                }

                if (in_array($newPhoneme, $phonemesArray)) {
                    $phonemes[$key] = $newPhoneme;
                } else{
                    unset($phonemes[$key]);
                }
            }
        }

        $normalizedTranscription = implode(self::$SPACE_DELIMITER, $phonemes);

        if ($transcription !== $normalizedTranscription) {
            Log::debug([
                'from' => $transcription,
                'to' => $normalizedTranscription
            ]);
        }

        return $normalizedTranscription;
    }

}

