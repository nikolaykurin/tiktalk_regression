<?php namespace Progforce\General\Components;

use DB;
use Config;
use Progforce\User\Classes\SLPAuth;
use Carbon\Carbon;
use Progforce\General\Classes\ReportsAdapter;
use Progforce\General\Components\PatientBaseComponent;
use Progforce\General\Models\PatientTreatmentPlan;
use Progforce\General\Models\RegisteredDevice;
use Progforce\General\Models\Sound;
use Progforce\General\Models\TreatmentPhase;
use Progforce\General\Models\TreatmentPlanPhase;
use Progforce\General\Models\SlpLog;
use Progforce\General\Models\TreatmentStatus;
use Progforce\User\Models\User;

class Patient extends PatientBaseComponent {

    public function componentDetails() {
        return [
            'name'        => 'Clinician Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties() {
        return [];
    }

    public function onRun() {
        $user = User::find($this->param('id'));

        $this->page['patient'] = $user;
        $this->page['patient']['last_session_duration'] = $user->getLastSessionDuration();
        $this->page['plans'] = $this->getPlans($user->id);

        $this->page['devices'] = $user->getRegisteredDeviceIdOptions();

        $this->setSlpLogs();

        $dt = Carbon::today();
        $dateRange = [$dt->toDateTimeString(), $dt->endOfDay()->toDateTimeString()]; 
        $reportsAdapter = new ReportsAdapter($user, 0, $dateRange);
        $this->page['reports'] = $reportsAdapter->getReports(1);
        $this->page['progress'] = $reportsAdapter->getProgressVals();
        $this->page['navId'] = 0;
    }

    public function onGetReports() {
        $patientId = \Request::get('patientId', 0);
        $dateRange = \Request::get('filterPeriod', []);
        $navId = \Request::get('navId', 0);
        $pageNum = \Request::get('pageNum', 1);
        $user = User::find($patientId);

        $reportsAdapter = new ReportsAdapter($user, $navId, $dateRange);
        $this->page['reports'] = $reportsAdapter->getReports($pageNum);
        $this->page['navId'] = $navId;
        return ['#partialTabReportsTabs' =>  $this->renderPartial('patient/tab-reports-tabs')];
    }

    public function onUpdatePlansSequence() {
        $rows = \Request::all();
        foreach ($rows as $id => $row) {
            DB::table('progforce_general_patient_treatment_plans')->
                where('id', $id)->
                update($row);
        }
    }

    public function onAddPhase() {
        $planId = \Request::get('planId', 0);
        $phaseId = \Request::get('phaseId', 0);
        $rowNum = \Request::get('rowNum', 1);
        $planPhase = new TreatmentPlanPhase();
        $planPhase->plan_id = $planId;
        $planPhase->phase_id = $phaseId;
        $planPhase->row_num = $rowNum;
        $planPhase->save();
        $this->setPlanPhases($planId);

        SlpLog::addPlanPhaseLog('added_phase', $planPhase->id);
        $this->setSlpLogs();

        $params = ['id' => $planPhase->id, 'rowNum' => $rowNum, 'phaseId' => $phaseId, 'statusId'=> 1];
        return [
            'id' => $planPhase->id,
            '#partialPhaseRow0' =>  $this->renderPartial('@list-phase-row', $params),
            '#partialSlpLogsStatic' => $this->renderPartial('patient/slp-logs-static'),
        ];
    }

    public function onRemovePhase() {
        $id = \Request::get('id', 0);
        SlpLog::addPlanPhaseLog('removed_phase', $id);
        TreatmentPlanPhase::removePhase($id);
        $this->setSlpLogs();
        return [
            '#partialSlpLogsStatic' => $this->renderPartial('patient/slp-logs-static'),
        ];
    }

    public function onUpdatePhases() {
        $phases = \Request::get('phases', []);
        foreach ($phases as $phase) {
            if (trim($phase['vals']['phase_status_date']) === '') {
                $phase['vals']['phase_status_date'] = null;
            }
            DB::table('progforce_general_treatment_plans_phases')->
                where('id', $phase['id'])->
                update($phase['vals']);
            SlpLog::addPlanPhaseLog('changed_phase', $phase['id']);
        }
        $this->setSlpLogs();
        return [
            '#partialSlpLogsStatic' => $this->renderPartial('patient/slp-logs-static'),
        ];
    }

    public function onUpdatePlan() {
        $planId = \Request::get('planId', 0);
        $planStatusId = \Request::get('planStatusId', 0);
        if ($planId) {
            PatientTreatmentPlan::where('id', $planId)->update(['protocol_status' => $planStatusId]);
            SlpLog::addPlanLog('changed_plan_status', $planId);
        }
        $this->setSlpLogs();
        return [
            '#partialSlpLogsStatic' => $this->renderPartial('patient/slp-logs-static'),
        ];
    }

    public function onInsertPlan() {
        $patientId =  \Request::get('patientId', 0);
        $planSoundId =  \Request::get('planSoundId', 0);

        $plan = PatientTreatmentPlan::where('user_id', $patientId)->
            where('sound_id', $planSoundId)->
            get()->
            first();

        if (!$plan) {
            $sequence = PatientTreatmentPlan::select('protocol_sequence')
                ->where('user_id', $patientId)
                ->orderByDesc('protocol_sequence')
                ->first();

            $plan = new PatientTreatmentPlan();
            $plan->user_id = $patientId;
            $plan->sound_id = $planSoundId;
            $plan->protocol_sequence = $sequence ? $sequence->protocol_sequence + 1 : 1;
            $plan->protocol_status = 1;
            $plan->save();
            SlpLog::addPlanLog('added_plan', $plan->id);
        }
        $this->setPlanPhases($plan->id);
        $this->page['planAdded'] = true;
        $this->page['plansSounds'] = PatientTreatmentPlan::getSounds($patientId);
        $this->page['plans'] = $this->getPlans($patientId);
        $this->setSlpLogs();
        return [
            '#partialPlansPhases' =>  $this->renderPartial('@tab-plans-phases'),
            '#partialPlansList' =>  $this->renderPartial('@tab-plans-list'),
            '#partialSlpLogsStatic' => $this->renderPartial('patient/slp-logs-static'),
        ];

    }

    public function onAddPlan() {
        $user = User::find($this->param('id'));

        $this->page['patient'] = $user;
        $plansSounds = PatientTreatmentPlan::getSounds($user->id)->toArray();

        $exclSoundIds = array_column($plansSounds, 's.sound_id');
        $qry = Sound::select('id', 'sound')->
            where('language_id', $user->language_id);
        if ($exclSoundIds) {
            $qry = $qry->whereNotIn('id', $exclSoundIds);
        }
        $this->page['sounds'] = $qry->distinct()->get();

        return ['#partialPlansAdd' =>  $this->renderPartial('@tab-plans-add')];
    }

    public function onEditPlan() {
        $planId = post('planId');
        $user = User::find($this->param('id'));
        $plan = PatientTreatmentPlan::find($planId);

        $this->page['planStatusId'] = $plan->protocol_status;
        $this->page['patient'] = $user;
        $this->page['plansSounds'] = PatientTreatmentPlan::getSounds($user->id);

        $this->setPlanPhases($planId);
        return ['#partialPlansPhases' =>  $this->renderPartial('@tab-plans-phases')];
    }

    public function onChangePlan() {
        $planId = \Request::get('planId', 0);
        $this->setPlanPhases($planId);
        return ['#partialPhases' =>  $this->renderPartial('@list-phases')];
    }

    public function onDeletePlan() {
        $id = \Request::get('planId', 0);
        SlpLog::addPlanLog('removed_plan', $id);
        PatientTreatmentPlan::destroy($id);

        $user = User::find($this->param('id'));
        $this->page['plans'] = $this->getPlans($user->id);
        $this->setSlpLogs();
        return [
            '#partialPlansList' =>  $this->renderPartial('@tab-plans-list'),
            '#partialSlpLogsStatic' => $this->renderPartial('patient/slp-logs-static'),
        ];
    }

    public function onGetSlpLogs() {
        $planId = \Request::get('planId', 0);
        $this->setSlpLogs($planId);
        return [
            '#partialSlpLogsModal' =>  $this->renderPartial('slp-logs-modal'),
            '#partialSlpLogsStatic' => $this->renderPartial('patient/slp-logs-static')
        ];
    }

    public function onSetSlpComment() {
        $id = \Request::get('logId', 0);
        $comment = \Request::get('comment', '');
        if ($id) {
            $log = SlpLog::find($id);
            if ($log) {
                $log->slp_comment = $comment;
                $log->save();
            }
        }
    }

    public function onUpdateDevice() {
        $device_id = (int) post('device_id');
        $user_id = (int) post('user_id');

        $user = User::find($user_id);
        $user->registered_device_id = $device_id === 0 ? null : $device_id;
        $user->save();
    }

    private function setPlanPhases($planId) {
        $this->page['planId'] = $planId;
        $this->page['phases'] = TreatmentPhase::all();
        $this->page['statuses'] = TreatmentStatus::all();
        $this->page['planPhases'] = TreatmentPlanPhase::getPlanPhases($planId);
    }

    private function getPlans($userId) {
        $plans = PatientTreatmentPlan::with('protocol_status_field')
            ->where('user_id', $userId)
            ->orderBy('protocol_sequence')
            ->get();
        return $plans;
    }

    private function setSlpLogs($planId= null) {
        $slp =  SLPAuth::check();
        if ($slp) {
            $qry = SlpLog::where('slp_id', $slp->id);
            if ($planId) {
                $qry = $qry->where('plan_id', $planId);
            }
            $this->page['slpLogs'] = $qry->
                orderBy('created_at', 'desc')->
                get();
            $this->page['actions'] = array_flip(Config::get('log.slp_actions'));
        }
    }

}
