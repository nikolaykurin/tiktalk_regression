<?php namespace Progforce\General\Classes\Helpers;

class ResponseHelper {

    public static function get400($message) {
        return response()->json(['success' => false, 'message' => $message], 400);
    }

    public static function get200(Array $params, $decode = false) {
        if ($decode) {
            return response()->json($params, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json($params, 200);
        }
    }
}
