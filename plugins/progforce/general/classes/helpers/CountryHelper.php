<?php namespace Progforce\General\Classes\Helpers;

class CountryHelper {

    public static function normalize($country) {
        switch ($country) {
            case 'USA':
                return 'US';
            default:
                return $country;
        }
    }

}
