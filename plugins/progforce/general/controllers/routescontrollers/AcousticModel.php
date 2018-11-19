<?php namespace Progforce\General\Controllers\RoutesControllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\AcousticModelAdapter;
use Chumper\Zipper\Facades\Zipper;
use Progforce\User\Models\User;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Illuminate\Support\Facades\Log;
use Exception;

class AcousticModel extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function generate(Request $request) {
        if (
            !$request->has('user_id')
        ||
            !$request->hasFile('recordings')
        ||
            $request->recordings->extension() !== 'zip'
        ) {
            return response('Request must contains \'user_id\' and \'recordings\' parameters zip array', 400);
        }

        $user_code = $request->input('user_id');
        $deviceTime = $request->input('device_time', Carbon::now()->format('Y-m-d H:i:s'));

        if ($user_code == Config::get('users.guest_id')) {
            return response('Guest user can\'t generate AM', 403);
        }

        $user = User::find($user_code);

        if (!$user) {
            return response('User with given ID not exists', 400);
        }

        try {
            $userAbsoluteModelPath = PathHelper::getUserAbsoluteModelPath($user_code);
            $language = $user->language->language;

            $userRelativeModelPath = PathHelper::getUserRelativeModelPath($user_code);
            $userAbsoluteRecordingsPath = PathHelper::getUserAbsoluteRecordingsPath($user_code);
            $recordingsFileName = $request->recordings->getClientOriginalName();

            $request->recordings->storeAs($userRelativeModelPath, $recordingsFileName);
            $zipPath = sprintf('%s/%s', $userAbsoluteModelPath, $recordingsFileName);
            Zipper::make($zipPath)->extractTo($userAbsoluteModelPath);
            unlink($zipPath);

            AcousticModelHelper::prepareFilesForAdapt($userAbsoluteModelPath, $language);

            shell_exec(sprintf('rm -rf %s/AM_%s_*', $userAbsoluteRecordingsPath, $user_code));
            $folderTime = Carbon::parse($deviceTime)->format('Y-m-d_H:i:s');
            $folderName = sprintf('AM_%s_%s', $user_code, $folderTime);
            $recordingsPath = $userAbsoluteRecordingsPath . DIRECTORY_SEPARATOR . $folderName;
            mkdir($recordingsPath);
            PathHelper::copyFilesByMask($userAbsoluteModelPath, $recordingsPath, '*.wav');

            $adapter = new AcousticModelAdapter($userAbsoluteModelPath, $language);
            $adapter->map_adaptation();
        } catch (Exception $e) {
            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return response($e->getMessage(), 400);
        }

        return response('Success');
    }

    public function download(Request $request) {
        try {
            $user_code = (int) $request->input('user_id');
            $downloadPath = AcousticModelHelper::generateUserModelZip($user_code);
        } catch (Exception $e) {
            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return response($e->getMessage(), 400);
        }

        return response()->download($downloadPath)->deleteFileAfterSend(true);
    }

    public function delete(Request $request) {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response('`userId` required', 400);
        }
        $user = User::find($userId);


        $path = sprintf('%s/%s_%s', PathHelper::getUserAbsoluteModelPath($user->id), AcousticModelAdapter::$map_adapt, $user->language->language);
        PathHelper::RMDIRRecursively($path);

        return back();
    }
}
