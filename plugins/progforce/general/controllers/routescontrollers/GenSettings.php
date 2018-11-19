<?php namespace Progforce\General\Controllers\RoutesControllers;

use Progforce\General\Models\GenConfig;
use Illuminate\Routing\Controller;

class GenSettings extends Controller {

    public function getActivationCode() {
        $code = GenConfig::getActivationCode();
        return response()->json($code, 200, [], JSON_UNESCAPED_UNICODE);
    }

}