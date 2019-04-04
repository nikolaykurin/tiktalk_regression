<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Progforce\General\Classes\AcousticModelCreator;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Exception;
use Progforce\General\Classes\Helpers\PathHelper;
use Illuminate\Support\Facades\Log;
use Chumper\Zipper\Facades\Zipper;

class NewAcousticModel extends Controller {

    public function generate(Request $request) {
        if (!$request->has('lang')) {
            return response('No \'lang\' param!');
        }
        if (!$request->hasFile('recordings')) {
            return response('\'recordings\' zip array required');
        }

        $language = $request->input('lang');
        $debug = config('training.DEBUG');

        $path = PathHelper::getModelTempPath($language);

        PathHelper::RMDIRRecursively($path);
        if (!is_dir($path)) {
            mkdir($path);
        }

        try {
            $recordingsFileName = $request->recordings->getClientOriginalName();
            $request->recordings->storeAs(sprintf('%s/%s', PathHelper::$BASE_TEMP_PATH, $language), $recordingsFileName);

            $zipPath = $path . DIRECTORY_SEPARATOR . $recordingsFileName;
            Zipper::make($zipPath)->extractTo($path);

            if (!$debug) {
                unlink($zipPath);
            }

            AcousticModelHelper::prepareFilesForCreate_v2($path, $language, $debug);

            $creator = new AcousticModelCreator($path, $language, $debug);
            $creator->create();
        } catch (Exception $e) {
            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            PathHelper::RMDIRRecursively($path);

            return response($e->getMessage(), 400);
        }

        return response('Success');
    }

}
