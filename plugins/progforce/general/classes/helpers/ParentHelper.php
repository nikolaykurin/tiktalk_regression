<?php namespace Progforce\General\Classes\Helpers;

use Progforce\General\Models\PatientParent;
use Illuminate\Support\Facades\Hash;

class ParentHelper {

    /**
     * Fake "Auth" method, we don't need to make session & etc., just find parent user by given credentials
     * @param $credentials
     * @return bool
     */
    public static function auth($credentials) {
        $username = array_get($credentials, 'username', null);
        $password = array_get($credentials, 'password', null);

        if (is_null($username) || is_null($password)) {
            return false;
        }

        $parent = PatientParent::where('username', $username)->first();

        if (!$parent) {
            return false;
        }

        if (!Hash::check($password, $parent->password)) {
            return false;
        }

        return $parent;
    }

}
