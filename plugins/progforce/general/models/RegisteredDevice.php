<?php namespace Progforce\General\Models;

use Model;
use Config;
use Progforce\User\Models\User;
use October\Rain\Database\Traits\Validation;

class RegisteredDevice extends Model
{
    use Validation;

    public $table = 'progforce_general_registered_devices';

    public $rules = [
        'device_id' => 'required|unique:progforce_general_registered_devices|max:255',
    ];

    protected $fillable = [
        'device_id'
    ];

    public $belongsTo = [
        'white_label' => ['Progforce\General\Models\WhiteLabel', 'key' => 'white_label_id'],
    ];

    public $hasMany = [
        'users' => User::class
    ];

    public static function getList() {
        $res = [];
        $codes = Config::get('tiktalk.device_codes');;
        $devices = RegisteredDevice::get();
        foreach ($devices as $device) {
            foreach ($codes as $code) {
                $key =  $code . '_id';
                if ($device->$key) {
                    $res[$device->id . '_' . $code] = $device->$key . ' ('.$code.')';
                }
            }
        }
        return $res;
    }

    public static function findByDeviceId($fld, $val) {
        return self::where($fld, $val)->first();
    }

    public static function findDevice($ids) {
        $device = null;

        $device = self::whereIn('mixed_id', array_values($ids))->first();
        if ($device) {
            return $device;
        }

        if ($ids['imei_id']) {
            $device = self::findByDeviceId('imei_id', $ids['imei_id']);
        }
        if (!$device) {
            $device = self::findByDeviceId('unity_id', $ids['unity_id']);
        }
        return $device;
    }

    public function updateIds($ids) {
        $isUpdated = false;
        foreach ($ids as $fld => $val) {
            if ($val && !$this->$fld) {
                $this->$fld = $val;
                $isUpdated = true;
            }
        }
        return $isUpdated;
    }

    public function getServerAttribute() {
        $servers = Config::get('tiktalk.servers');
        $server = array_get($servers, $this->server_id, null);
        return $server ? $server['code'] . ': ' . $server['url'] : '';
    }

    public function getServerIdOptions() {
        $servers = array_column(Config::get('tiktalk.servers'), 'code', 'id');
        return $servers;
    }

    public function getUsers() {
        $thumb_width = 124;
        $thumb_height = 124;
        $users = $this->users;

        $result = [];
        foreach ($users as $user) {
            $avatar = null;

            if (!empty($user->avatar)) {
                $thumb = $user->avatar->getThumb($thumb_width, $thumb_height, ['mode' => 'crop']);
                $thumb = str_replace(url('/'), base_path(), $thumb);

                $avatar = base64_encode(file_get_contents($thumb));
            }

            $result[] = [
                'user_code' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'language_id' => $user->language_id,
                'user_avatar' => $avatar
            ];
        }

        return $result;
    }

    public function getWhiteLabel() {
        $res = null;
        if ($this->is_white_label && $this->white_label) {
            $path = $this->white_label->label_image->getPath();
            $path = str_replace(url('/'), base_path(), $path);
            $res = base64_encode(file_get_contents($path));
        }
        return $res;
    }

    public function getWhiteLabelIdOptions($keyValue = null) {
        $labels = WhiteLabel::select('id', 'name')->get()->toArray();
        $res = [0 => '-- None --'] + array_column($labels, 'name', 'id');
        return $res;
    }
}
