<?php

namespace Progforce\User\Classes;

use Cookie;
use Illuminate\Support\Facades\Hash;
use October\Rain\Auth\AuthException;
use Progforce\User\Models\SLP;
use Session;

class SLPAuth
{

    public static $sessionKey = 'slp_auth';

    public static function check() {
        if (
            !($userArray = Session::get(self::$sessionKey)) &&
            !($userArray = Cookie::get(self::$sessionKey))
        ) {
            return false;
        }

        if (!is_array($userArray) || count($userArray) !== 2) {
            return false;
        }

        list($id, $persistCode) = $userArray;

        /*
         * Look up user
         */
        if (!$user = SLP::find($id)) {
            return false;
        }

        /*
         * Confirm the persistence code is valid, otherwise reject
         */
        if (!$user->checkPersistCode($persistCode)) {
            return false;
        }

        /*
         * Pass
         */
        return $user;
    }

    public static function authenticate(array $credentials, $remember = true)
    {
        /*
         * Look up the user by authentication credentials.
         */
        try {
            $user = self::findUserByCredentials($credentials);
        } catch (AuthException $ex) {
            throw $ex;
        }

        self::login($user, $remember);

        return $user;
    }

    public static function findUserByCredentials($credentials) {
        $query = SLP::where('user_name', $credentials['login']);
        if (!$user = $query->first()) {
            throw new AuthException('A user was not found with the given credentials!');
        }
        if(!Hash::check($credentials['password'], $user->password)) {
            throw new AuthException('A user was not found with the given credentials.');
        }

        return $user;
    }

    public static function login($user, $remember) {
        $toPersist = [$user->getKey(), $user->getPersistCode()];
        Session::put(self::$sessionKey, $toPersist);
        if ($remember) {
            Cookie::queue(Cookie::forever(self::$sessionKey, $toPersist));
        }
    }

    public static function logout() {
        Session::forget(self::$sessionKey);
        Cookie::queue(Cookie::forget(self::$sessionKey));
    }
}