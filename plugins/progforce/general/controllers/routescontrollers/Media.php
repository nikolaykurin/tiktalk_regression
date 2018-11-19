<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Chumper\Zipper\Zipper;
use Exception;

class Media extends Controller {

    public function download(Request $request) {
        try {

            $mediaFolder = $request->input('folder');

            $path = implode(DIRECTORY_SEPARATOR,
                [
                    storage_path(),
                    'app',
                    'media',
                    trim($mediaFolder, '/')
                ]);
            $zipName = $path . DIRECTORY_SEPARATOR . 'files.zip';

            $imagesList = glob($path);

            $zipper = new Zipper();
            $zipper->make($zipName)->add($imagesList);
            $zipper->close();

        } catch (Exception $e) {
            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return response($e->getMessage(), 400);
        }

        return response()->download($zipName)->deleteFileAfterSend(true);
    }

}
