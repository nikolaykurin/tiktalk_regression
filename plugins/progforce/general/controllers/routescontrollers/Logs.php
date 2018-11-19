<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Progforce\General\Models\Log;

class Logs extends Controller
{

    public function save(Request $request) {
        $input = $request->all();
        $user_id = $input['user_id'];
        unset($input['user_id']);

        $logActions = Config::get('log.actions');

        Log::create([
            'user_id' => $user_id,
            'datetime' => date('Y-m-d H:i:s'),
            'action' => $logActions['recordings'],
            'data' => json_encode($input)
        ]);

        return response('ok');
    }

}