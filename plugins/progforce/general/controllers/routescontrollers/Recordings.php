<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Chumper\Zipper\Zipper;
use Progforce\User\Models\User;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Models\Report;
use Progforce\General\Classes\Helpers\ResponseHelper;

class Recordings extends Controller
{

    public function store(Request $request) {
        tracelog($request->all());
        if  (!$request->hasFile('recordings') || $request->recordings->extension() !== 'zip') {
            return ResponseHelper::get400('Recordings field with zip array required!');
        }

        $userId = $request->input('user_id');
        $deviceTime = $request->input('device_time', Carbon::now()->format('Y-m-d H:i:s'));

        if (!$userId || !User::find($userId)) {
            return ResponseHelper::get400('Wrong user Id!');
        }

        $gameName = $request->input('game_name', '');
        $recResults = $this->parseRecResults($request);

        try {
            $recordingsFileName = $request->recordings->getClientOriginalName();
            $request->recordings->storeAs(PathHelper::$BASE_TEMP_PATH, $recordingsFileName);

            $userAbsoluteRecordingsPath = PathHelper::getUserAbsoluteRecordingsPath($userId);
            $folderTime = Carbon::parse($deviceTime)->format('Y-m-d_H:i:s');
            $folderName =  sprintf('REC_%s_%s', $userId, $folderTime);
            $recordingsPath = $userAbsoluteRecordingsPath . DIRECTORY_SEPARATOR . $folderName;
            mkdir($recordingsPath);

            $zipPath = PathHelper::getBaseTempPath() . DIRECTORY_SEPARATOR . $recordingsFileName;
            $zipper = new Zipper();
            $zipper->make($zipPath)->extractTo($recordingsPath);
            unlink($zipPath);
            $this->insertReports($userId, $folderName, $gameName, $recResults);
        } catch (\Exception $e) {
            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return ResponseHelper::get400($e->getMessage());
        }

        return response('Success');
    }

    public function downloadAM(Request $request) {
        if (empty($userId = $request->input('user_id'))) {
            return response('\'user_id\' field required');
        }

        $folders = glob(PathHelper::getUserAbsoluteRecordingsPath($userId) . '/' . 'AM_*');

        usort( $folders, function( $a, $b ) { return filemtime($b) - filemtime($a); } );

        $path = $folders[0];
        $zipName = PathHelper::getBaseTempPath() . DIRECTORY_SEPARATOR . substr($path, strrpos($path, '/') + 1) . '.zip';
        $files = glob($path);

        $zipper = new Zipper();
        $zipper->make($zipName)->add($files);
        $zipper->close();

        return response()->download($zipName)->deleteFileAfterSend(true);
    }

    public function downloadTraining(Request $request) {
        if (empty($userId = $request->input('user_id'))) {
            return response('\'user_id\' field required');
        }

        $folders = glob(PathHelper::getUserAbsoluteRecordingsPath($userId) . '/' . 'REC_*');

        $zipName = PathHelper::getBaseTempPath() . DIRECTORY_SEPARATOR . 'REC_' . $userId . '.zip';
        $zipper = (new Zipper())->make($zipName);
         foreach ($folders as $folder) {
             $zipper = $zipper->folder(basename($folder))->add($folder);
         }
        $zipper->close();

        return response()->download($zipName)->deleteFileAfterSend(true);
    }

    private function getDeviceTime($userTimeZone) {
        try {
            $res = Carbon::now($userTimeZone);
        } catch (\Exception $ex) {
            traceLog($ex->getMessage());
            $res = Carbon::now();
        }
        return $res;
    }

    private function parseRecResults(Request $request) {
        $res = [];
        $recResults = $request->input('recognition_result', '');
        $results = explode(';', $recResults);
        foreach ($results as $result) {
            $fileScore = explode(':', $result);
            $res[] = ['fileName'=> $fileScore[0], 'resultId' => $fileScore[1]];
        }
        return $res;
    }

    private function insertReports($userId, $folderName, $gameName, array $recResults) {
        $reports = [];
        $games = array_column(\Config::get('tiktalk.games'), 'id', 'code');
        $gameId = array_get($games, strtolower($gameName), 0);

        foreach ($recResults as $result) {
            $reports[] = [
                'user_id' => $userId,
                'game_id' => $gameId,
                'folder_name' => $folderName,
                'file_name' => $result['fileName'],
                'result_id' => $result['resultId'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        Report::insert($reports);
    }
}