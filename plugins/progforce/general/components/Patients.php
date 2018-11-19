<?php namespace Progforce\General\Components;

use Progforce\User\Classes\SLPAuth;
use Progforce\General\Components\PatientBaseComponent;
use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\Helpers\PatientTreatmentPlanHelper;
use Progforce\User\Controllers\RoutesControllers\Account;
use Tymon\JWTAuth\Facades\JWTAuth;

class Patients extends PatientBaseComponent {


    public function componentDetails() {
        return [
            'name'        => 'Clinician Patients Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties() {
        return [];
    }

    public function onRun() {
        $searchText = \Request::get('q', '');
        $this->page['searchText'] = $searchText;
        $this->page['patientList'] = $this->preparePatients($searchText);
        $this->page['pushToken'] = Config::get('jwt.push_token');
        $this->page['jitsiToken'] = $this->makeJWT($this->page['user']);
    }

    private function preparePatients($search = null) {
        $patientsQuery = $this->page['user']->patients();
        if (!is_null($search)) {
            $searchString = sprintf('%%%s%%', $search);
            $patientsQuery = empty($search) ? $patientsQuery : $patientsQuery
                ->where('first_name', 'like', $searchString)
                ->orWhere('last_name', 'like', $searchString);
        }
        $patients = $patientsQuery->paginate(10);
        $patients->appends(['q' => $search])->links();

        foreach ($patients as $key => $patient) {

            $planHelper = new PatientTreatmentPlanHelper($patients[$key]['id']);
            $plan = $planHelper->plan;
            $phase = $planHelper->getCurrentTreatmentPhase();

            $patients[$key]['token'] = Account::makeJWT($patient->id);
            $patients[$key]['is_guest'] = $patient->id === (int) Config::get('users.guest_id');
            $patients[$key]['treatment_sound'] = $plan ? $plan->sound : null;
            $patients[$key]['treatment_phase'] = $phase ? $phase->description : null;
            $patients[$key]['last_session_duration'] = $patient->getLastSessionDuration();
        }
        return $patients;
    }

    public function onSearch() {
        $searchText = \Request::get('text', '');
        $this->page['user'] = \Progforce\User\Models\SLP::find(\Request::get('user_id'));
        $this->page['patientList'] = $this->preparePatients($searchText);
        $this->page['searchText'] = $searchText;
        $this->page['pushToken'] = Config::get('jwt.push_token');
        $this->page['jitsiToken'] = $this->makeJWT($this->page['user']);
    }

    public function onGetChatTokens() {
        $patientId = \Request::get('patientId', 0);
        $patient = \Progforce\User\Models\User::find($patientId);
        $user =  SLPAuth::check();
        $jitsiToken = $user ? $this->makeJWT($user) : '';
        $token = Account::makeJWT($patientId);
        $pushToken = Config::get('jwt.push_token');
        $device = $patient->device_id;
        return compact('token', 'pushToken', 'jitsiToken', 'device');
    }

    public function makeJWT($slp) {
        if (!$slp)
            return response('User not found', 400);
        $token = JWTAuth::fromUser($slp, [
            'iss' => '*',
            'room' => '*',
            'aud' => '*'
        ]);
        return $token;
    }


}
