<?php namespace Progforce\General\Classes\Helpers;

class FileHelper {

    public static function getFileNameWithoutExtension($filename) {
        return pathinfo($filename)['filename'];
    }

}
