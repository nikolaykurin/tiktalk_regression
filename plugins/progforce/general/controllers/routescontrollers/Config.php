<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config as SystemConfig;
use Illuminate\Http\Request;

class Config extends Controller
{
    public function download(Request $request) {
        return response()->download(base_path() . DIRECTORY_SEPARATOR . SystemConfig::get('paths.config'));
    }

}