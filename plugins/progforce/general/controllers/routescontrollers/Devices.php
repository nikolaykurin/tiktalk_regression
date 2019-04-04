<?php namespace Progforce\General\Controllers\RoutesControllers;

use Config;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Progforce\General\Models\RegisteredDevice;
use Progforce\General\Classes\Helpers\ResponseHelper;

class Devices extends Controller
{

    static $defaultActiveVersion = 'Not Installed';

    private function getDeviceIdsParams(Request $request) {
        $params = [];
        if ( !$request->has('unity_id') || 
             !$request->input('unity_id')) {
            return [];
        }
        $codes = Config::get('tiktalk.device_codes');
        foreach ($codes as $code) {
            $params[$code . '_id'] = $request->input($code . '_id', '');
        }
        return $params;
    }

    public function get(Request $request) {
        $servers = Config::get('tiktalk.servers');
        if (!$servers) {
            return ResponseHelper::get400('Servers List is Empty!');
        }

        $deviceIds = $this->getDeviceIdsParams($request);
        if (!$deviceIds) {
            return ResponseHelper::get400('Device Id required!');
        }
        $device = $this->findOrCreateDevice($deviceIds, $servers);

        $users = $device->getUsers();
        if (!$users) {
            return ResponseHelper::get400('There are no users attached to this device!');
        }

        $count = [];

        $languageCodes = array_map(function ($item) use (&$count) {
            $code = explode('-', $item)[0];
            $count[$code] = 0;
            return $code;
        }, array_column(config('languages'), 'code', 'id'));

        foreach ($users as $user) {
            $langCode = $languageCodes[$user['language_id']];
            $count[$langCode] = $count[$langCode] + 1;
        }

        $params = [
            'is_white_label' => $device->is_white_label, 
            'white_label' => $device->getWhiteLabel(), 
            'users' => $users,
            'count' => $count
        ];
        return ResponseHelper::get200($params, true);
    }

    public function getDeviceServer(Request $request) {
        $servers = Config::get('tiktalk.servers');
        if (!$servers) {
            return ResponseHelper::get400('Servers List is Empty!');
        }

        $deviceIds = $this->getDeviceIdsParams($request);
        if (!$deviceIds) {
            return ResponseHelper::get400('Device Id required!');
        }
        $device = $this->findOrCreateDevice($deviceIds, $servers, $request->input('active_version'));

        $server = array_get($servers, $device->server_id, null);
        if (!$server) {
            return ResponseHelper::get400('Server not found!');
        }
        return  ResponseHelper::get200(['server' => $server]);
    }

    private function findOrCreateDevice($deviceIds, $servers, $activeVersion = null) {
        $device = RegisteredDevice::findDevice($deviceIds);

        if (!$device) {
            $device = new RegisteredDevice();
            $device->updateIds($deviceIds);

            // TO-DO remove device_id and mixed_id, serach on unity_id
            $codes = Config::get('tiktalk.device_codes');
            foreach ($codes as $code) {
                $key = $code . '_id';
                if ($deviceIds[$key]) {
                    $device->device_id = $deviceIds[$key];
                    $device->mixed_id = $deviceIds[$key];
                    break;
                }
            }

            $device->server_id = $servers[1]['id'];
            $device->white_label_id = 0;
            $device->active_version = $activeVersion ? sprintf('%s %s', $activeVersion, $servers[1]['code']) : self::$defaultActiveVersion;

            $needSave = true;
        } else {
            $needSave = $device->updateIds($deviceIds);

            if ($activeVersion) {
                $device->active_version = sprintf('%s %s', $activeVersion, array_get($servers, $device->server_id)['code']);

                $needSave = true;
            }
        }
        if ($needSave) { $device->save(); }
        return $device;
    }

}
