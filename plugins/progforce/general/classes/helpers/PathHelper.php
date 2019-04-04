<?php namespace Progforce\General\Classes\Helpers;

use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\AcousticModelAdapter;
use Progforce\User\Models\User;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

// TODO: simplify all methods with `mkdir`!
class PathHelper {

    public static $BASE_USERS_PATH = 'app/users';
    public static $BASE_LANGUAGE_PATH = 'app/languages';
    public static $BASE_TEMP_PATH = 'temp';
    public static $PERFECT_MODEL = 'perfect_model';

    public static function getAbsoluteUserPath($user_id) {
        return sprintf('%s/%s/%s', storage_path(), self::$BASE_USERS_PATH, $user_id);
    }

    public static function getImagesPath($langCode) {
        return sprintf('media/words/' . $langCode . '/images');
    }

    public static function getWordsAudioPath($langCode) {
        return sprintf('media/words/' . $langCode . '/audio');
    }

    public static function getAbsoluteImagesPath($langCode) {
        return sprintf('%s/app/%s', storage_path(), self::getImagesPath($langCode));
    }

    public static function getAbsoluteWordsAudioPath($langCode) {
        return sprintf('%s/app/%s', storage_path(), self::getWordsAudioPath($langCode));
    }

    public static function getAbsoluteSoundClipPath($langCode, $sound) {
        return sprintf('%s/app/media/sounds/clips/%s/%s', storage_path(), $langCode, $sound);
    }

    public static function getUserAbsolutePathTo($user_id, $folder) {
        $path = sprintf('%s/%s/%s/%s', storage_path(), self::$BASE_USERS_PATH, $user_id, $folder);

        if (!is_dir($path)) {
            mkdir($path, 0777,true);
        }

        return $path;
    }

    public static function getUserAbsoluteModelPath($user_id) {
        return self::getUserAbsolutePathTo($user_id, 'model');
    }

    public static function getUserAbsoluteModelPathForLanguage($user_id) {
        $user = User::findOrFail($user_id);

        return sprintf('%s/%s_%s', self::getUserAbsoluteModelPath($user->id), AcousticModelAdapter::$map_adapt, $user->language->language);
    }

    public static function getUserAbsoluteRecordingsPath($user_id) {
        return self::getUserAbsolutePathTo($user_id, 'recordings');
    }

    public static function getUserRelativeRecordingsPath($userId) {
        $path = sprintf('/storage/app/users/%s/recordings', $userId);
        return $path;
    }

    public static function getUserRelativeModelPath($user_id) {
        $path = sprintf('%s/%s/model', 'users', $user_id);

        return $path;
    }

    public static function getWordImagePath($langCode, $wordId, $isAbs = false) {
        $res = '';
        $imagesPath = PathHelper::getImagesPath($langCode);
        $path = $isAbs ? self::getAbsoluteImagesPath($langCode)  : '/storage/app/' . $imagesPath;
        if (\Storage::exists($imagesPath . '/' . $wordId . '.png')) {
            $res =  $path . '/' .  $wordId . '.png';
        }
        return $res;
    }

    public static function getWordAudioPath($langCode, $wordId, $isAbs = false) {
        $res = '';
        //$sfx example _he
        $sfx = $langCode == 'en-us' ? '' : '_' . substr($langCode, 0, 2);
        $audioPath = PathHelper::getWordsAudioPath($langCode);
        $audioAbsPath = PathHelper::getAbsoluteWordsAudioPath($langCode);

        $baseName = $wordId . '_audio' . $sfx;
        $fileName = $baseName . '.mp3';
        $path = $isAbs ? self::getAbsoluteWordsAudioPath($langCode)  : '/storage/app/' . $audioPath;
        if (\Storage::exists($audioPath . '/' . $fileName)) {
            $res = $path . '/' . $fileName;
        } else {
            $fileName = $baseName . '.wav';
            if (\Storage::exists($audioPath . '/' . $fileName)) {
                $res = $path . '/' . $fileName;
            }
        }
        return $res;
    }

    public static function getBaseTempPath() {
        $path = storage_path() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . self::$BASE_TEMP_PATH;

        if (!is_dir($path)) {
            mkdir($path, 0777,true);
        }

        return $path;
    }

    public static function getResponseTempPath() {
        $path = sprintf('%s/%s/%s', storage_path(), self::$BASE_TEMP_PATH, 'response');

        if (!is_dir($path)) {
            mkdir($path, 0777,true);
        }

        return $path;
    }

    public static function getLanguageAbsolutePath($language_id) {
        $path = sprintf('%s/%s/%s', storage_path(), self::$BASE_LANGUAGE_PATH, $language_id);

        if (!is_dir($path)) {
            mkdir($path, 0777,true);
        }

        return $path;
    }

    public static function getFilesPath() {
        $path = sprintf('%s/%s', base_path(), Config::get('paths.files'));

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    public static function copyFilesByMask($from, $to, $mask) {
        $process = new Process(
            sprintf('find %s -name "%s" -exec cp \'{}\' %s \;', $from, $mask, $to)
        );
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public static function checkDirectoryEmpty($dir) {
        if (!is_readable($dir)) {
            return true;
        }

        return count(scandir($dir)) === 2;
    }

    public static function clearDirectory($dir) {
        $process = new Process(
            sprintf('rm -f %s/*', $dir)
        );
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            Log::debug(sprintf('Can\'t clear %s directory'), $process->getErrorOutput());
        }
    }

    public static function getPerfectSpeakerAbsoluteModelPath() {
        $path = sprintf('%s/%s/%s', storage_path(), 'app', self::$PERFECT_MODEL);

        if (!is_dir($path)) {
            mkdir($path, 0777,true);
        }

        return $path;
    }

    public static function RMDIRRecursively($dir) {
        if (is_dir($dir)) {
            shell_exec(sprintf('rm -rf %s', $dir));
        }
    }

    public static function getModelTempPath($lang) {
        $path = self::getBaseTempPath() . DIRECTORY_SEPARATOR . $lang;

        return $path;
    }

    public static function getFolders($dir) {
        $scan = scandir($dir);

        return array_filter($scan, function ($item) use ($dir) {
            return !in_array($item, [ '.', '..' ]) && is_dir($dir . DIRECTORY_SEPARATOR . $item);
        });
    }

    public static function getPathSize($path) {
        $bytes = 0;
        $path = realpath($path);

        if ($path !== false && $path !='' && file_exists($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytes += $object->getSize();
            }
        }

        return $bytes;
    }
}
