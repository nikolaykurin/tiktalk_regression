<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Progforce\General\Classes\Helpers\ConfigHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Classes\Helpers\ResponseHelper;
use Progforce\General\Models\GenConfig;

class Versions extends Controller {

    public function fileUpload() {
        $file = Input::file('file');
        $filename = $file->getClientOriginalName();
        $version = Input::get('version');
        if (strpos($filename, '.apk')) {
            $filename = Config::get('constants.speachservice');
        }

        $dlcPath = PathHelper::getFilesPath();
        $infoFile = sprintf('%s/%s', $dlcPath, Config::get('constants.files_info'));

        $content = file_exists($infoFile) ? json_decode(file_get_contents($infoFile), true) : [];
        $content[$filename]['version'] = $version;

        $fp = fopen($infoFile, 'w');
        fwrite($fp, json_encode($content));
        fclose($fp);

        $file->move($dlcPath, $filename);

        return redirect()->to('/');
    }

    public function fileDelete() {
        $name = Input::get('name');
        $file = sprintf('%s/%s', PathHelper::getFilesPath(), $name);

        if (!file_exists($file)) {
            throw new \ValidationException(['File not exists']);
        }

        $infoFile = sprintf('%s/%s', PathHelper::getFilesPath(), Config::get('constants.files_info'));
        $content = json_decode(file_get_contents($infoFile), true);

        if (!array_key_exists($name, $content)) {
            throw new ValidationException(['Info file has not an information about this file']);
        }

        unset($content[$name]);
        unlink($file);

        $fp = fopen($infoFile, 'w');
        fwrite($fp, json_encode($content));
        fclose($fp);

        return \Redirect::to('/');
    }

    public function getVersion(Request $request) {
        if (!$name = $request->input('name')) {
            return response('\'name\' field required', 400);
        }

        $infoFile = sprintf('%s/%s', PathHelper::getFilesPath(), Config::get('constants.files_info'));

        if (!is_file($infoFile)) {
            return response('Info file not found!', 400);
        }

        $content = json_decode(file_get_contents($infoFile), true);

        if (!array_key_exists($name, $content)) {
            return response('No info by given file name', 400);
        }

        $version = $content[$name]['version'];

        return response()->json(['version' => $version, 'file' => $name]);
    }

    public function downloadFile(Request $request) {
        $name = $request->input('name');
        $file = sprintf('%s/%s', PathHelper::getFilesPath(), $name);

        return response()->download($file);
    }

    public function getVersions(Request $request) {
        $version = $request->input('version');

        if (is_null($version)) {
            return ResponseHelper::get400('"version" parameter required!');
        }

        if (!in_array($version, config('versions'))) {
            return ResponseHelper::get400('wrong "version" parameter value!');
        }

        $config = GenConfig::get();

        return $config->{ConfigHelper::getVersionKey($version)};
    }

}
