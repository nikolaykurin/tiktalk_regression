<?php

namespace Progforce\General\Classes\Helpers;


use Chumper\Zipper\Zipper;
use Illuminate\Support\Facades\Storage;
use Progforce\General\Models\Word;
use Progforce\General\Models\WordHeIl;

class GoogleDriveHelper
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
}