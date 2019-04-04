<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Exception;
use Progforce\General\Classes\Helpers\ResponseHelper;

class Android extends Controller
{
    public function uploadSpeechanalyzerApk(Request $request) {
        return $this->uploadApk($request, Config::get('constants.speechanalyzer_name'));
    }

    public function downloadDebugApk() {
        return $this->downloadApk('/storage/app/app-debug.apk');
    }

    public function downloadSpeechanalyzerApk() {
        return $this->downloadApk(Config::get('constants.speechanalyzer_name'));
    }

    public function uploadGameApk(Request $request) {
        return $this->uploadApk($request, Config::get('constants.game_name'));
    }

    public function downloadGameApk() {
        return $this->downloadApk(Config::get('constants.game_name'));
    }

    public function uploadApk(Request $request, $filename) {
        if (!$request->hasFile('apk')) {
            return response('Request must contain \'apk\' field', 400);
        }

        $file = $request->file('apk');

        if ($file->getClientOriginalExtension() !== 'apk') {
            return response('Only \'*.apk\' files are supported', 400);
        }

        try {
            $file->move(base_path(), $filename);
        } catch (Exception $e) {
            return response($e->getMessage(), 400);
        }

        return response('Success');
    }

    public function downloadApk($filename) {
        $filePath = sprintf('%s/%s', base_path(), $filename);

        if  (!file_exists($filePath)) {
            return response('File not exists', 400);
        }

        return response()->download($filePath);
    }

    public function downloadMobilePrivacyPolicy() {
        $filePath = sprintf('%s/%s/%s', base_path(), 'files', 'mobile-privacy-policy.doc');

        if  (!file_exists($filePath)) {
            return ResponseHelper::get400('File not exists');
        }

        return response()->download($filePath);
    }
}
