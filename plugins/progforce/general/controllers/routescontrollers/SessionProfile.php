<?php namespace Progforce\General\Controllers\RoutesControllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\AcousticAdapter;
use Progforce\General\Classes\Helpers\IssueHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Models\Word;
use Progforce\User\Models\User;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Exception;

class SessionProfile extends Controller
{
    public static $ZIP_ACOUSTIC_MODEL_NAME = 'model.zip';

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|string
     */
    public function downloadAcousticModel(Request $request) {
        $tempPath = PathHelper::getResponseTempPath();
        $sphinxPath = Config::get('paths.pocketsphinx');
        $acousticModelPath = sprintf('%s/%s', $sphinxPath, 'model');
        $acousticModelZipPath = sprintf('%s/%s', $tempPath, self::$ZIP_ACOUSTIC_MODEL_NAME);

        try {
            if (!is_dir($acousticModelPath)) {
                return IssueHelper::getIssue(IssueHelper::ISSUE_AM_NOT_FOUND);
//                throw new Exception(sprintf('No PocketSphinx acoustic model directory! Check that %s directory is not empty!', Config::get('paths.pocketsphinx')));
            }

            if (
                !file_exists($acousticModelZipPath)
                ||
                Carbon::createFromTimestamp(filemtime($acousticModelZipPath)) < Carbon::createFromTimestamp(filemtime($acousticModelPath))
            ) {
                $acousticModelZip = new ZipArchive();
                $acousticModelZip->open($acousticModelZipPath, ZipArchive::CREATE);

                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($acousticModelPath), RecursiveIteratorIterator::SELF_FIRST);

                foreach ($files as $file) {
                    $file = str_replace('\\', '/', $file);

                    if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                        continue;
                    }

                    $file = realpath($file);

                    if (is_dir($file)) {
                        $acousticModelZip->addEmptyDir(str_replace($acousticModelPath . '/', '', $file . '/'));
                    } else if (is_file($file)) {
                        $acousticModelZip->addFromString(str_replace($acousticModelPath . '/', '', $file), file_get_contents($file));
                    }
                }

                $acousticModelZip->close();
            }
        } catch (Exception $e) {
            return IssueHelper::issueAMNotFound();
//            return response($e->getMessage(), 400);
        }

//        return response('Debug OK!');
        return response()->download($acousticModelZipPath);
    }

    /**
     * @param $user_code
     * @return string
     */
    public function getZipResponseName($user_code) {
        return sprintf('profile_%s.zip', $user_code);
    }
}