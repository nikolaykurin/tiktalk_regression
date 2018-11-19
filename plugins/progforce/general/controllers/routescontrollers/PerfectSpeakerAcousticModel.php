<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Progforce\General\Classes\AcousticModelAdapter;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Exception;
use Zipper;

class PerfectSpeakerAcousticModel extends Controller {

    public function generate(Request $request) {
        if  (!$request->hasFile('recordings') || $request->recordings->extension() !== 'zip') {
            return response('\'recordings\' field with zip array required', 400);
        }

        $language = $request->input('lang', 'en-us');
        $path = PathHelper::getPerfectSpeakerAbsoluteModelPath();

        try {
            $recordingsFileName = $request->recordings->getClientOriginalName();
            $request->recordings->storeAs(PathHelper::$PERFECT_MODEL, $recordingsFileName);

            $zipPath = $path . DIRECTORY_SEPARATOR . $recordingsFileName;
            Zipper::make($zipPath)->extractTo($path);
            unlink($zipPath);

            if ($language === 'he-il') {
                AcousticModelHelper::prepareFilesForAdapt_v2($path, $language);
            } else {
                AcousticModelHelper::prepareFilesForAdapt($path, $language);
            }

            $folders = PathHelper::getFolders($path);
            foreach ($folders as $folder) {
                if (strpos($folder, AcousticModelAdapter::$map_adapt) !== false) {
                    continue;
                }

                PathHelper::RMDIRRecursively(sprintf('%s/%s', $path, $folder));
            }

            $adapter = new AcousticModelAdapter($path, $language);
            $adapter->map_adaptation();
        } catch (Exception $e) {
            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return response($e->getMessage(), 400);
        }

        return response('Success');
    }

    public function download(Request $request) {
        try {
            $lang = $request->input('lang', 'en-us');
            $downloadPath = AcousticModelHelper::generatePerfectModelZip($lang);
        } catch (Exception $e) {
            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return response($e->getMessage(), 400);
        }

        return response()->download($downloadPath);
    }

    public function downloadDict(Request $request) {
        $langCode = $request->input('lang', '');
        if (!$langCode) {
            return response('Language Code Required!', 400);
        }
        $dictPath = sprintf(
            '%s/%s/%s/%s', 
            Config::get('paths.pocketsphinx'), 
            'model',
            $langCode, 
            sprintf('cmudict-%s.dict', $langCode)
        );
        return response()->download($dictPath, 'dictionary.dict');
    }

    public function delete(Request $request) {
        $lang = $request->input('lang', 'en-us');
        $path = sprintf('%s/%s_%s', PathHelper::getPerfectSpeakerAbsoluteModelPath(), AcousticModelAdapter::$map_adapt, $lang);
        $zip = sprintf('%s.zip', $path);

        PathHelper::RMDIRRecursively($path);
        if (file_exists($zip)) {
            unlink($zip);
        }

        return response('Success');
    }

}