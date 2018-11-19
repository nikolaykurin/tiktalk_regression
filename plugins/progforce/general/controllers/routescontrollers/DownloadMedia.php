<?php namespace Progforce\General\Controllers\RoutesControllers;

use Chumper\Zipper\Zipper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Models\LanguageConfiguration;

class DownloadMedia extends Controller
{

    public function downloadStorageFolder(Request $request) {
        $fld = \Request::get('fld', '');

        if (!$fld) {
            return response('fld required!');
        }

        $path = sprintf('%s/app/%s', storage_path(), $fld);
        $zipName = PathHelper::getBaseTempPath() . DIRECTORY_SEPARATOR . $fld . '.zip';
        $files = glob($path . '/' . '*');

        if (!$files) {
            return response('Files not found!');
        }

        $zipper = new Zipper();
        $zipper->make($zipName)->add($files);
        $zipper->close();

        return response()->download($zipName)->deleteFileAfterSend(true);
    }

    public function downloadSoundClips(Request $request) {
        $langId = \Request::get('lang_id', 0);
        $soundId = \Request::get('sound_id', 0);
        if (!$soundId) {
            return response('sound id required!');
        }

        if (!$langId) {
            return response('language id required!');
        }
        $language = LanguageConfiguration::find($langId);
        if (!$language) {
            return response('language not found!');
        }

        if (!$soundId) {
            return response('sound id required!');
        }

        $path = PathHelper::getAbsoluteSoundClipPath($language->language, $soundId);
        $zipName = PathHelper::getBaseTempPath() . DIRECTORY_SEPARATOR . $soundId . '.zip';
        $files = glob($path . '/' . '*');

        if (!$files) {
            return response('Clips for sound "' . $soundId . '" '. $language->language . ' not found!');
        }

        $zipper = new Zipper();
        $zipper->make($zipName)->add($files);
        $zipper->close();

        return response()->download($zipName)->deleteFileAfterSend(true);
    }

    public function downloadTraining(Request $request) {
        if (empty($userId = $request->input('user_id'))) {
            return response('\'user_id\' field required');
        }

        $folders = glob(PathHelper::getUserAbsoluteRecordingsPath($userId) . '/' . 'REC_*');

        $zipName = PathHelper::getBaseTempPath() . DIRECTORY_SEPARATOR . 'REC_' . $userId . '.zip';
        $zipper = (new Zipper())->make($zipName);
         foreach ($folders as $folder) {
             $zipper = $zipper->folder(basename($folder))->add($folder);
         }
        $zipper->close();

        return response()->download($zipName)->deleteFileAfterSend(true);
    }

}