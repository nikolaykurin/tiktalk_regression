<?php

namespace Progforce\General\Classes\Helpers;

use Chumper\Zipper\Zipper;
use Illuminate\Support\Facades\Storage;
use Progforce\General\Models\Word;
use Progforce\General\Models\WordHeIl;

class MediaHelper
{

    /**
     * @param $drive
     * @return static
     */
    public static function getFilesList($drive) {
        $dir = '/';
        $recursive = false; // Get subdirectories also?
        $contents = collect($drive->listContents($dir, $recursive));
        return $contents->where('type', '=', 'file'); // files
    }

    /**
     * @param $IDList
     * @param $drive
     * @return array
     */
    public static function getFilteredList($IDList, $drive) {
        $filesList = self::getFilesList($drive);
        self::loadAssetsFromGDToStorage($drive, $filesList);
        $filteredList = [];
        foreach ($IDList as $item) {
            $file = $filesList->where('filename', 'like', $item['word_id'])->first();
            if(count($file)) {
                $filteredList[] = $file;
            }
        }
        return $filteredList;
    }

    /**
     *
     */
    public static function loadImagesToDB() {
        $drive = Storage::disk('google');
        $IDList = WordHeIl::select('word_id')->get()->toArray();
        $filteredList = self::getFilteredList($IDList, $drive);
        foreach ($filteredList as $item) {
            WordHeIl::where('word_id', 'like', $item)->update(['image_1' => $item['filename']]);
        }
    }

    public static function loadImagesFromMediaToDB() {
        $files = glob('storage/app/media/words/assets/*.png');
        foreach ($files as $file) {
            $fileName = str_replace('.png', '', str_replace('storage/app/media/words/assets/', '', $file));
            WordHeIl::where('word_id', 'like', $fileName)->update(['image_1' => $fileName]);
        }
    }

    public static function loadAudioToDB() {
        $files = glob('storage/app/media/*.mp3');
        foreach ($files as $file) {
            $fileName = str_replace('.mp3', '', str_replace('storage/app/media/', '', $file));
            $fileID = str_replace('_audio_he', '', $fileName);
            WordHeIl::where('word_id', 'like', $fileID)->update(['audio_1' => $fileName]);
        }
    }

    /**
     * @param $drive
     * @param $filesList
     */
    public static function loadAssetsFromGDToStorage($drive, $filesList) {
        foreach ($filesList as $item) {
            $fileName = 'media/'.$item['filename'].'.'.$item['extension'];
            $exists = Storage::disk('local')->exists($fileName);
            if(!$exists) {
                $file = $drive->get($item['path']);
                Storage::disk('local')->put($fileName, $file);
            }
        }
    }

    private static function getImages($wordsCodes, $langCode) {
        $allFiles = glob(PathHelper::getAbsoluteImagesPath($langCode) . '/*');
        $files = array_filter($allFiles, function($assetPath) use ($wordsCodes) {
            foreach($wordsCodes as $wordCode) {
                $exists = strpos($assetPath, '/' . $wordCode) !== false;
                if ($exists) {
                    return true;
                }
            }
            return false;
        });
        return $files;
    }

    private static function getWordsAudio($wordsCodes, $langCode) {
        $allFiles = glob(PathHelper::getAbsoluteWordsAudioPath($langCode) . '/*');
        $files = array_filter($allFiles, function($filePath) use ($wordsCodes) {
            foreach($wordsCodes as $wordCode) {
                $exists = strpos($filePath, '/' . $wordCode) !== false;
                if ($exists) {
                    return true;
                }
            }
            return false;
        });
        return $files;
    }

    public static function getWordsMedia($wordsCodes, $langCode) {
        $imagesFiles = self::getImages($wordsCodes, $langCode);
        $audoFiles = self::getWordsAudio($wordsCodes, $langCode);
        $mediaFiles = array_merge($imagesFiles, $audoFiles);

        Storage::disk('local')->delete('temp/test.zip');
        $zipper = new Zipper;
        $zipPath = 'storage/app/temp/test.zip';
        $zipper->make($zipPath)->add($mediaFiles);
        $zipper->close();

        return $zipPath;
    }

    public static function renameAudio() {
        $filesList = glob('storage/app/media/*.mp3');
        foreach($filesList as $file) {
            if(!Storage::disk('local')->exists(str_replace('_audio', '',
                    str_replace('.mp3', '',
                        str_replace('_audio', '',
                            str_replace('storage/app/', '', $file)))) . '_audio.mp3')) {
                Storage::disk('local')->move(str_replace('storage/app/', '', $file),
                    str_replace('_audio', '',
                        str_replace('.mp3', '',
                            str_replace('_audio', '',
                                str_replace('storage/app/', '', $file)))) . '_audio.mp3');
            }
        }
        return true;
    }

}