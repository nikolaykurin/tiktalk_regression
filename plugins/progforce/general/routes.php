<?php
/**
 * Test routes
 */

Route::prefix('results')->group(function () {
    Route::get('generate', 'Progforce\General\Controllers\RoutesControllers\Results@generate');
    Route::get('fill', 'Progforce\General\Controllers\RoutesControllers\Results@fill');
    Route::get('clear', 'Progforce\General\Controllers\RoutesControllers\Results@clear');
    Route::get('get', 'Progforce\General\Controllers\RoutesControllers\Results@get');
    Route::get('make_data', 'Progforce\General\Controllers\RoutesControllers\Results@make_data');

    Route::prefix('model')->group(function () {
        Route::get('build', 'Progforce\General\Controllers\RoutesControllers\Results@build');
        Route::post('predict', 'Progforce\General\Controllers\RoutesControllers\Results@predict');
    });
});


Route::get('/qa', function () {
    traceLog('qa started');
    dd(Carbon\Carbon::parse('2018-09-26 08:48:55')->format('Y-m-d_H:i:s'));
});

Route::get('/getuserslistTest', function () {
    $users = \Progforce\User\Models\User::select('id', 'code', 'first_name')->get();
    return $users;
});

//Route::post('/update_game_mode', 'progforce\user\controllers\routescontrollers\Account@updateGameMode');

/**
 * Upload/Download Android APK file
 */
Route::post('/upload_apk', 'Progforce\General\Controllers\RoutesControllers\Android@uploadSpeechanalyzerApk');
Route::get('/download_apk', 'Progforce\General\Controllers\RoutesControllers\Android@downloadSpeechanalyzerApk');
Route::get('/download_apk_debug', 'Progforce\General\Controllers\RoutesControllers\Android@downloadDebugApk');
Route::post('/upload_game', 'Progforce\General\Controllers\RoutesControllers\Android@uploadGameApk');
Route::get('/download_game', 'Progforce\General\Controllers\RoutesControllers\Android@downloadGameApk');
Route::get('/privacy', 'Progforce\General\Controllers\RoutesControllers\Android@downloadMobilePrivacyPolicy');

// Gen Settings
Route::get('/get_activation_code', 'Progforce\General\Controllers\RoutesControllers\GenSettings@getActivationCode');

/**
 * Get game & speachservice version
 */
Route::post('/versioncontroller/upload', 'Progforce\General\Controllers\RoutesControllers\Versions@fileUpload');
Route::get('/versioncontroller/delete', 'Progforce\General\Controllers\RoutesControllers\Versions@fileDelete');
Route::get('/get_version', 'Progforce\General\Controllers\RoutesControllers\Versions@getVersion');

Route::get('/download_file', 'Progforce\General\Controllers\RoutesControllers\Versions@downloadFile');

/**
 * Get parsed from file language files
 */
Route::get('/generate_dictionaries', 'Progforce\General\Controllers\RoutesControllers\Languages@generateDictionaries');
Route::get('/get_lang_file', 'Progforce\General\Controllers\RoutesControllers\Languages@downloadLangFile');
//Route::post('/put_lang_file', 'Progforce\General\Controllers\RoutesControllers\Languages@uploadLangFile');

/**
 * Redirect to backend  from root URL
 */
Route::get('/', function() {
    return \Redirect::to('clinician/patients', 301);
});

Route::get('/clinician', function () {
    return \Redirect::to('clinician/patients', 301);
});

Route::group([
    'middleware' => ['web', 'Progforce\User\Classes\BackendAuthMiddleware']
], function () {
    /**
     * Database Import Tool page
     */
    Route::get('/tool', function () {
        $tool_entry_point = \Illuminate\Support\Facades\Config::get('paths.tool_entry_point');

        if (!file_exists($tool_entry_point)) {
            return response(sprintf('Can\'t find Import Tool entry point file! [%s]', $tool_entry_point));
        }

        ob_start();
        require($tool_entry_point);
        return ob_get_clean();
    });
    /**
     * Allows database tool call methods from interaction.php
     */
    Route::any('/interaction.php', function () {
        require('import_tool/interaction.php');

        return $_REQUEST['inter']($_REQUEST);
    });

    Route::any('/wimport', 'Progforce\General\Controllers\Words@import');
    Route::any('/download-en', 'Progforce\General\Controllers\Words@loadFileEn');
    Route::any('/download-he', 'Progforce\General\Controllers\WordsHeIl@loadFileHe');

    Route::any('/report-sound-count', 'progforce\report\classes\ReportsHelper@getCountSoundAndPosition');
    Route::any('/report-words', 'progforce\report\classes\ReportsHelper@getWordsReport');
});

Route::post('/loadaudios', 'Progforce\General\Controllers\Words@onLoadHebAudio_2');

Route::group([
    'middleware' => ['web', 'Progforce\User\Classes\AuthMiddleware']
], function () {
    /**
     *  request:
     * {
     *    'user_id' => id,
     *    'recordings' => name.zip - must contains "fileids.fileids" and "transcriptions.transcription" files
     * }
     * response:
     * {
     *    'Success'
     * }
     */
    Route::post('/generate_acoustic_model', 'Progforce\General\Controllers\RoutesControllers\AcousticModel@generate');

//    Route::any('/update_game_mode', 'progforce\user\controllers\routescontrollers\Account@updateGameMode');

});

Route::any('/set_user_activity', 'progforce\user\controllers\routescontrollers\Account@setActivity');

Route::any('/update_game_mode', 'progforce\user\controllers\routescontrollers\Account@updateGameMode');

// get_words_media?codes=[100028,100032]&lang_id=1
Route::any('/get_words_media', 'Progforce\General\Controllers\Words@getMedia');

/**
 * Download user's acoustic model
 */
Route::get('/download_am', 'Progforce\General\Controllers\RoutesControllers\AcousticModel@download');
Route::get('/delete_am', 'Progforce\General\Controllers\RoutesControllers\AcousticModel@delete');
Route::post('/generate_perfect_acoustic_model', 'Progforce\General\Controllers\RoutesControllers\PerfectSpeakerAcousticModel@generate');
Route::get('/download_perfect_am', 'Progforce\General\Controllers\RoutesControllers\PerfectSpeakerAcousticModel@download');
Route::get('/delete_perfect_am', 'Progforce\General\Controllers\RoutesControllers\PerfectSpeakerAcousticModel@delete');

Route::get('/download_dict', 'Progforce\General\Controllers\RoutesControllers\PerfectSpeakerAcousticModel@downloadDict');

Route::get('/download_storage', 'Progforce\General\Controllers\RoutesControllers\DownloadMedia@downloadStorageFolder');
Route::get('/download_sound_clips', 'Progforce\General\Controllers\RoutesControllers\DownloadMedia@downloadSoundClips');

Route::post('/new_model', 'Progforce\General\Controllers\RoutesControllers\NewLanguageModel@generate');

Route::post('/store_recordings', 'Progforce\General\Controllers\RoutesControllers\Recordings@store');
Route::get('/download_am_recordings', 'Progforce\General\Controllers\RoutesControllers\Recordings@downloadAM');
Route::get('/download_training_recordings', 'Progforce\General\Controllers\RoutesControllers\Recordings@downloadTraining');

Route::get('/get_words', 'Progforce\General\Controllers\RoutesControllers\Words@get');

Route::get('/get_users', 'Progforce\General\Controllers\RoutesControllers\Devices@get');
Route::get('/get_device_server', 'Progforce\General\Controllers\RoutesControllers\Devices@getDeviceServer');
Route::any('/guest/set_lang', 'Progforce\User\Controllers\RoutesControllers\Account@setGuestLang');
Route::get('/log', 'Progforce\General\Controllers\RoutesControllers\Logs@save');

Route::get('log/{lang}', 'Progforce\General\Controllers\RoutesControllers\AMLogs@index');
Route::get('log/{lang}/download', 'Progforce\General\Controllers\RoutesControllers\AMLogs@download');

Route::get('/scoring_algorithms', 'Progforce\General\Controllers\RoutesControllers\ScoringAlgorithms@get');

Route::get('/config', 'Progforce\General\Controllers\RoutesControllers\Config@download');

Route::get('/media/download', 'Progforce\General\Controllers\RoutesControllers\Media@download');

Route::get('/dictionaries/status', 'Progforce\General\Controllers\RoutesControllers\DictionariesStatus@get');
Route::get('/dictionaries/build', 'Progforce\General\Controllers\RoutesControllers\DictionariesStatus@build');

Route::prefix('versions')->group(function () {
    Route::get('get', 'Progforce\General\Controllers\RoutesControllers\Versions@getVersions');
});

Route::get('/patient/info', 'Progforce\General\Controllers\RoutesControllers\PatientInfo@get');
Route::get('/patient/mail', 'Progforce\General\Controllers\RoutesControllers\PatientInfo@mail');

