<?php namespace Progforce\General\Classes\Helpers;


class ConfigHelper {

    public static function getVersionKey($key) {
        return sprintf('version_%s', $key);
    }

}
