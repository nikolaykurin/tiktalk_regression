<?php namespace Backend\Database\Seeds;

use Illuminate\Support\Facades\Config;
use Seeder;
use Progforce\User\Models\User;

class SeedGuestUser extends Seeder
{
    public static $first_name = 'Guest';
    public static $last_name = null;
    public static $age = 21;
    public static $slp_first_name = 'Guest';
    public static $slp_id = 1;
    public static $language_id = 2; // he-il
    public static $country_id = 2;  // Israel
    public static $registered_device_id = null;

    public function run() {
        $id = Config::get('users.guest_id');

        User::create([
            'id' => $id,
            'code' => $id,
            'is_activated' => true,
            'activated_at' => date('Y-m-d H:i:s'),
            'first_name' => self::$first_name,
            'last_name' => self::$last_name,
            'age' => self::$age,
            'slp_first_name' => self::$slp_first_name,
            'slp_id' => self::$slp_id,
            'country_id' => self::$country_id,
            'language_id' => self::$language_id,
            'registered_device_id' => self::$registered_device_id
        ]);
    }
}
