<?php namespace Progforce\General\Controllers\RoutesControllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Progforce\General\Classes\Helpers\ParentHelper;
use Progforce\General\Classes\Helpers\ResponseHelper;
use Progforce\General\Classes\ReportsAdapter;
use Progforce\General\Models\GenConfig;
use Progforce\General\Models\PatientTreatmentPlan;
use Progforce\General\Models\Session;
use Illuminate\Support\Facades\Mail;
use Exception;

class PatientInfo extends Controller {

    private static $SUBJECT = 'TikTalk mail notification';

    public function get(Request $request) {
        if (!$parent = ParentHelper::auth($request->all())) {
            return ResponseHelper::get400('Wrong credentials!');
        }

        $childrenArray = $parent->children;

        if (!$childrenArray->count()) {
            return ResponseHelper::get400('Parent has no children!');
        }

        $children = [];
        foreach ($childrenArray as $user) {
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            Carbon::setWeekEndsAt(Carbon::SATURDAY);

            $progressVals = (new ReportsAdapter($user, 0, []))->getProgressVals()['vals'];

            $plansArray = PatientTreatmentPlan::with([ 'phases', 'sound', 'protocol_status_field' ])
                ->where('user_id', $user->id)
                ->get([ 'sound_id', 'created_at', 'id', 'protocol_status' ]);

            $plans = [];
            foreach ($plansArray as $plan) {
                $plans[] = [
                    'created_at' => (string) $plan->created_at,
                    'status' => $plan->protocol_status_field->description,
                    'sound' => $plan->sound->sound,
                    'progress' => round($progressVals[$plan->sound_id]['completed'] / $progressVals[$plan->sound_id]['total'], 2)
                ];
            }

            $sessionsArray = Session::where('datetime_start', '>', Carbon::now()->startOfWeek()->subWeek()->format('Y-m-d H:i:s'))
                ->where('datetime_end', '<', Carbon::now()->startOfWeek()->format('Y-m-d H:i:s'))
                ->where('user_id', $user->id)
                ->get()
                ->toArray();

            $sessionsCount = count($sessionsArray);
            $sessionsFullDuration = 0;

            array_walk($sessionsArray, function ($item) use (&$sessionsFullDuration) {
                $diffInMinutes = Carbon::createFromFormat('Y-m-d H:i:s', $item['datetime_start'])->diffInMinutes(Carbon::createFromFormat('Y-m-d H:i:s', $item['datetime_end']));
                if ($diffInMinutes > 2) {
                    $sessionsFullDuration += $diffInMinutes;
                }
            });

            $children[] = [
                'name' => $user->first_name,
                'plans' => $plans,
                'sessions' => [
                    'count' => $sessionsCount,
                    'avg_duration' => $sessionsCount > 0 ? round($sessionsFullDuration / $sessionsCount, 2) : 0
                ]
            ];
        }

        return ResponseHelper::get200([
            'lang' => $childrenArray->first()->language->language,
            'children' => $children
        ]);
    }

    public function mail(Request $request) {
        $data = $request->all();
        $config = GenConfig::get()->toArray();

        if (empty($data['from']) || empty($data['content'])) {
            return ResponseHelper::get400('"from" and "content" params required!');
        }

        if (empty($mailFrom = array_get($config, 'mail_from'))) {
            return ResponseHelper::get400('Set "Sender E-Mail" in Backend config');
        }
        if (empty($mailTo = array_get($config, 'mail_to'))) {
            return ResponseHelper::get400('Set "Recipient E-Mail" in Backend config!');
        }

        try {
            Mail::send('progforce.general::emails.message', $data, function ($message) use ($data, $mailFrom, $mailTo) {
                $message->subject(self::$SUBJECT);
                $message->from($mailFrom);
                $message->to($mailTo);
            });
        } catch (Exception $e) {
            ResponseHelper::get400($e->getMessage());
        }

        return response('OK');
    }

}
