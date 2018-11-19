<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Progforce\General\Classes\Helpers\ResponseHelper;
use Progforce\General\Models\ScoringAlgorithm;

class ScoringAlgorithms extends Controller {

    public function get() {
        $langId = \Request::get('lang_id', null);
        if (!$langId) {
            return ResponseHelper::get400('Language Id field required!');
        }
        $algorithms = ScoringAlgorithm::
            select('id', 'field', 'value')->
            where('language_id', $langId)->
            get()->
            toArray();
        return ResponseHelper::get200(compact('algorithms'));
    }

}