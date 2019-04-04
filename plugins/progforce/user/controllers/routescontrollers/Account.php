<?php namespace Progforce\User\Controllers\RoutesControllers;

use Progforce\General\Classes\Helpers\ResponseHelper;
use Progforce\General\Classes\AcousticModelAdapter;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Classes\Helpers\WordsHelper;
use Progforce\General\Models\GenConfig;
use Progforce\General\Models\LanguageConfiguration;
use Progforce\General\Models\PatientTreatmentPlan;
use Progforce\General\Models\Session;
use Progforce\General\Models\Sound;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\Helpers\IssueHelper;
use Progforce\General\Classes\Helpers\PatientTreatmentPlanHelper;
use Progforce\General\Models\Log;
use Progforce\User\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class Account extends Controller
{

    private function getSounds($langId, Array $soundIds) {
        $sounds = Sound::select('id', 'sound', 'words_count')->
            where('language_id', $langId)->
            whereIn('id', $soundIds)->
            get()->
            toArray();
        return $sounds;
    }

    public function authenticate(Request $request) {
        $data = $request->all();

        if (empty($data['user_id'])) {
            return IssueHelper::getIssue(IssueHelper::ISSUE_USER_NOT_FOUND, $data);
        }

        if (empty($data['device_time'])) {
            return ResponseHelper::get400('"device_time" field required!');
        }

        $user = User::find($data['user_id']);

        if (!$user) {
            return IssueHelper::getIssue(IssueHelper::ISSUE_USER_NOT_FOUND, $data);
        }

        if ($user->isGuest()) {
            return response()->json(self::sendGuestResponse($user));
        } else {
            $user->is_active = true;
            if (!empty($data['device_id'])) {
                $user->device_id = $data['device_id'];
            }
            $user->save();
            $planHelper = new PatientTreatmentPlanHelper($user->id);
            $phase = $planHelper->getCurrentTreatmentPhase();
            $soundIds = [];
            if ($planHelper->plan) {
                if($phase && $phase->id == 1) { //for 1st phase AM acquisition
                    $soundIds = $planHelper->getSoundsByUserFor1stPhase();
                } else {
                    $soundIds = [$planHelper->plan->sound_id];
                }
            }
            $sounds = $soundIds ? $this->getSounds($user->language_id, $soundIds) : [];

            $session = Session::create([
               'user_id' => $user->id,
               'datetime_start' => $data['device_time']
            ]);

            return response()->json(
                self::sendResponse($user, $planHelper->plan, $phase, $sounds, true, $data['device_time'], $session->id),
                200,
                [],
                JSON_UNESCAPED_UNICODE
            );
        }
    }

    public function updateGameMode(Request $request) {
        $data = $request->all();

        if (empty($data['device_time'])) {
            return ResponseHelper::get400('"device_time" field required!');
        }

        $session_id = array_get($data, 'session_id', null);

        if (is_null($session_id) || !$session = Session::find($session_id)) {
            return ResponseHelper::get400('"session_id" field required!');
        }

        $user = $session->user;

        if (!$user) {
            return IssueHelper::getIssue(IssueHelper::ISSUE_USER_NOT_FOUND, $data);
        }

        $planHelper = new PatientTreatmentPlanHelper($user['id']);
        $planHelper->updatePlan(3, $session_id);
        $phase = $planHelper->getCurrentTreatmentPhase();

        if (!$planHelper->nextPhase) {
            return IssueHelper::getIssue(IssueHelper::ISSUE_NO_TREATMENT_PLAN);
        }

        $soundIds = [];
        if ($planHelper->plan) {
            $soundIds = [$planHelper->plan->sound_id];
        }
        $sounds = $this->getSounds($user->language_id, $soundIds);

        return response()->json(
            self::sendResponse($user, $planHelper->plan, $phase, $sounds, true, $data['device_time'], $session_id),
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    public function setActivity(Request $request) {
        $data = $request->all();

        if (empty($data['device_time'])) {
            return ResponseHelper::get400('"device" time field required!');
        }

        $session_id = array_get($data, 'session_id', null);

        if (is_null($session_id) || !$session = Session::find($session_id)) {
            return ResponseHelper::get400('"session_id" field required!');
        }

        $user = $session->user;

        if (!$user) {
            return IssueHelper::getIssue(IssueHelper::ISSUE_USER_NOT_FOUND. $data);
        }

        $isActive = (bool) array_get($data, 'is_active', false);

        $user->is_active = $isActive;
        if (!$isActive) {
            $user->last_seen = $request->input('device_time', date('Y-m-d H:i:s'));
        }
        $user->save();

        if (!$isActive) {
            $logActions = Config::get('log.actions');

            Log::create([
                'user_id' => $user->id,
                'session_id' => $session_id,
                'datetime' => date('Y-m-d H:i:s'),
                'action' => $logActions['logout'],
                'data' => null
            ]);

            Session::where('id', $session_id)->update([
                'datetime_end' => $data['device_time']
            ]);
        }

        return response()->json(['success' => true, 'is_active' => $isActive], 200);
    }

    public static function sendResponse($user, $plan, $phase, Array $sounds, $auth = false, $deviceTime = null, $session_id = null) {
        if (!$phase) {
            return IssueHelper::getIssue(IssueHelper::ISSUE_NO_TREATMENT_PLAN);
        }

        if ($phase->game_mode_id != 1 && !$user->hasModel()) {
            return IssueHelper::getIssue(IssueHelper::ISSUE_AM_NOT_FOUND);
        }

        $config = GenConfig::get();
        $wordsTable = '\\Progforce\\General\\Models\\'.$user->language->words_table;
        $words = new $wordsTable();
        $selectedWords = $words->getByPhase($phase, $sounds);
        if (!in_array($phase->id, [ 
            PatientTreatmentPlanHelper::$BENCHMARKING_TUNING, 
            PatientTreatmentPlanHelper::$BENCHMARKING 
        ])) {
            $defWordsCount = $phase->id == 1 ? null : 30;
            $selectedWords = self::filterPhaseWords($selectedWords, $sounds, $defWordsCount);
        }
        if ($plan && !$plan->is_multisound && $phase->id !== 1) {
            $selectedWords = self::filterMultisoundWords($selectedWords, $plan, $user);
        }
        $selectedWords = array_values($selectedWords);

        if ($phase->id == 1 && count($selectedWords) < $config->issue_not_enough_words_am) { //for 1st phase AM acquisition
            return IssueHelper::getIssue(IssueHelper::ISSUE_NOT_ENOUGH_WORDS_AM);
        } elseif (count($selectedWords) < $config->issue_not_enough_words_level) {
            return IssueHelper::getIssue(IssueHelper::ISSUE_NOT_ENOUGH_WORDS_LEVEL);
        }

        $token = self::makeJWT($user->id);//JWTAuth::fromUser($user, );

        $langCode = $user->language->language;
        $dictPath = Config::get('paths.pocketsphinx') . '/model/'. $langCode . '/cmudict-' . $langCode. '.dict';
        $perfectAMPath = sprintf('%s/%s_%s', PathHelper::getPerfectSpeakerAbsoluteModelPath(), AcousticModelAdapter::$map_adapt, $langCode);;

        $response = [
            'dictId' => md5_file($dictPath),
            'perfectSize' => PathHelper::getPathSize($perfectAMPath),
            'sound' => $sounds,
            'phase' => $phase->id,
            'token' => $token,
            'user_id' => $user->id,
            'code' => $user->id,
            'first_name' => $user->first_name,
            'language_id' => $user->language_id,
            'gameModeID' => $phase->id === PatientTreatmentPlanHelper::$BENCHMARKING ? 1 : $phase->game_mode_id,
            'wordsList' => $selectedWords,
            'developer_mode' => $user->developer_mode,
            'session_id' => $session_id,
            'has_plan' => true
        ];

        if ($auth) {
            $logActions = Config::get('log.actions');
            $date = $deviceTime ?? date('Y-m-d H:i:s');

            $user->last_login = $date;
            $user->save();

            Log::create([
                'user_id' => $user->id,
                'session_id' => $session_id,
                'datetime' => $date,
                'action' => $logActions['login'],
                'data' => json_encode(array_merge($response, [ 'phase' => (array)$phase ]))
            ]);
        }

        return $response;
    }

    private static function getFakeWords() {
        $selectedWords = \Progforce\General\Models\Word::from('progforce_general_words as w')
                ->select('w.word_id', 'w.word', 'w.transcription1', 's.sound')
                ->leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'sound_id')
                ->whereIn('w.id', [1,5,19,24,32])
                ->where('w.has_audio', true)
                ->where('w.transcription1', '!=', '')
                ->get()->toArray();
        $selectedWords = array_values($selectedWords);
        traceLog('---selected words-----');
        traceLog($selectedWords);
        return $selectedWords;
    }

    private static function filterPhaseWords($words, $sounds, $defCount) {
        $foundSounds = [];
        foreach ($sounds as $sound) {
            $snd = strtolower($sound['sound']);
            $foundSounds[$snd] = [
                'words_count' => $defCount ? $defCount : $sound['words_count'],
                'words_found' => 0
            ];
        }

        $res = [];
        foreach ($words as $word) {
            $snd = strtolower($word['sound']);
            if ($foundSounds[$snd]['words_count'] > $foundSounds[$snd]['words_found']) {
                $foundSounds[$snd]['words_found'] += 1;

                $newWord = $word;
                unset($newWord['sound']);
                $res[] = $newWord;
            } elseif (count($foundSounds) == 1) {
                break;
            }
        }
        return $res;
    }

    private static function filterMultisoundWords($words, $plan, $user) {
        $soundsInNextPlans = array_map(function ($item) {
            return $item['sound_id'];
        }, PatientTreatmentPlan::getAllNextByUser($user->id, $plan->id)->toArray());
        $wordIdsInNextPhases = WordsHelper::getWordIdsForSounds($user->language->words_table, $soundsInNextPlans);

        $words = array_filter($words, function ($item) use ($wordIdsInNextPhases) {
            return !in_array($item['word_id'], $wordIdsInNextPhases);
        });

        // restore keys after filter
        sort($words);

        return $words;
    }

    public static function sendGuestResponse($user) {
        $wordsTable = '\\Progforce\\General\\Models\\' . $user->language->words_table;
        $wordsTable = new $wordsTable();

        $selectedWords = $wordsTable->select('word_id', 'transcription1')
            ->distinct()
            ->where('has_audio', true)
            ->where('transcription1', '!=', '')
            ->orderByRaw('RAND()');

        $selectedWords = $selectedWords->get()->toArray();

        $selectedWords = array_values($selectedWords);

        $langCode = LanguageConfiguration::getLangCode($user->language_id);
        self::setAssetsSizes($langCode, $selectedWords);

        $response = [
            'token' => null,
            'user_id' => $user->id,
            'code' => $user->id,
            'first_name' => $user->first_name,
            'language_id' => $user->language_id,
            'gameModeID' => null,
            'wordsList' => $selectedWords,
            'developer_mode' => $user->developer_mode,
            'has_plan' => false,
        ];

        return $response;
    }

    public function setGuestLang(Request $request) {
        $input = $request->all();

        $guest = User::getGuest();
        $guest->language_id = $input['lang'];
        $guest->save();

        return response();
    }

    public static function makeJWT($userId) {
        $user = User::find($userId);
        if (!$user)
            return response('User not found', 400);
        $token = JWTAuth::fromUser($user, [
            'iss' => '*',
            'room' => '*',
            'aud' => '*'
        ]);
        return $token;
    }
}
