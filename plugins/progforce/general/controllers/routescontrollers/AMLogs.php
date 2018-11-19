<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Progforce\General\Classes\Helpers\PathHelper;
use Illuminate\Http\Request;
use Chumper\Zipper\Zipper;
use Exception;

class AMLogs extends Controller
{

    public function index($lang) {
        $path = PathHelper::getModelTempPath($lang);
        $temp = sys_get_temp_dir();
        $file = $lang . '.html';

        $indexFile = $path . DIRECTORY_SEPARATOR . $file;
        $tempFile = $temp . DIRECTORY_SEPARATOR . $file;

        if (!is_file($indexFile)) {
            echo '<h1 align="center">Logs are not ready now</h1>';
            return;
        }

        $content = file_get_contents($indexFile);
        $content = str_replace('file://' . base_path(), url('/'), $content);

        file_put_contents($tempFile, $content);

        return response()->file($tempFile);
    }

    public function download($lang) {
        try {
            $temp = sys_get_temp_dir();
            $date = date('Y-m-d_H-i-s');
            $path = PathHelper::getModelTempPath($lang);

            mkdir($temp . DIRECTORY_SEPARATOR . $date);

            $zip = $temp . DIRECTORY_SEPARATOR . $date . '.zip';
            $logs = $temp . DIRECTORY_SEPARATOR . $date;

            system(sprintf('cp -r %s/logdir %s/logdir', $path, $logs));
            system(sprintf('cp %s/%s.html %s/%s.html', $path, $lang, $logs, $lang));

            $index = $logs . DIRECTORY_SEPARATOR . $lang . '.html';

            $content = file_get_contents($index);
            $content = str_replace('file://' . $path, '.', $content);

            file_put_contents($index, $content);

            $zipper = new Zipper();
            $zipper->make($zip)->add(glob($logs));
            $zipper->close();

            system(sprintf('rm -rf %s', $logs));
        } catch (Exception $e) {
            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return response($e->getMessage(), 400);
        }

        return response()->download($zip)->deleteFileAfterSend(true);
    }

}