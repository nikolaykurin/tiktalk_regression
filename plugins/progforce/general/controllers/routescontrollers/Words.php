<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Progforce\General\Classes\Helpers\ResponseHelper;
use Progforce\General\Models\LanguageConfiguration;

class Words extends Controller
{

    public function get(Request $request) {
        $langId = $request->input('lang_id', 1);
        $wordsModel = LanguageConfiguration::getWordModel($langId);

        if (!$wordsModel) {
            return ResponseHelper::get400('Wrong Language Id!');
        }

        $words = new $wordsModel();
        $selectedWords = $words->getByPhase();
        if (!$selectedWords) {
            return ResponseHelper::get400('There are no words!');
        }

        return ResponseHelper::get200(
            ['wordsList' => array_values($selectedWords)], true
        );
    }

}
