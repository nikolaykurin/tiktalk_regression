<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Models\LanguageConfiguration;

class DictionariesStatus extends Controller {

    public function get(Request $request) {
        $langs = LanguageConfiguration::all();

        $result = [];
        foreach ($langs as $lang) {
            $result['discrepancies'] = array_merge($result['discrepancies'] ?? [], AcousticModelHelper::checkDictionaryForDiscrepancies($lang));
            $result['phonemes_dict'] = array_merge($result['phonemes_dict'] ?? [], array_map(function ($item) use ($lang) {
                return [
                    'phoneme' => $item,
                    'language' => $lang->language
                ];
            }, AcousticModelHelper::checkDictionaryPhonemes($lang)));
            $result['phonemes_db'] = array_merge($result['phonemes_db'] ?? [], array_map(function ($item) use ($lang) {
                return [
                    'phoneme' => $item,
                    'language' => $lang->language
                ];
            }, AcousticModelHelper::checkDBPhonemes($lang)));
        }

        return response()->json($result);
    }

    public function build(Request $request) {
        $langs = LanguageConfiguration::all();

        foreach ($langs as $lang) {
            AcousticModelHelper::fixDictionary($lang);
        }

        return response('OK');
    }

}
