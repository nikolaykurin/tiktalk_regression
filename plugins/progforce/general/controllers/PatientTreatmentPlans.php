<?php namespace Progforce\General\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Progforce\General\Models\TreatmentPlanPhase;
use Progforce\General\Models\PatientTreatmentPlan;

/**
 * Patient Treatment Plans Back-end Controller
 */
class PatientTreatmentPlans extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $planPhases;
    public $phases;
    public $statuses;

    public function __construct()
    {
        parent::__construct();
        $this->addJs('/plugins/progforce/general/assets/js/backend.js');

        $this->phases= \Progforce\General\Models\TreatmentPhase::get();
        $this->statuses = \Progforce\General\Models\TreatmentStatus::get();

        BackendMenu::setContext('Progforce.General', 'general', 'patienttreatmentplans');
    }

    public function update($id) {
        parent::update($id);
        $this->planPhases = TreatmentPlanPhase::getPlanPhases($id);
    }

    public function getPlanPhases() {
        return TreatmentPlanPhase::getPlanPhases($this->id);
    }

    public function onPlanPhaseModify($plan_id) {
        $planPhaseId = \Request::get('plan_phase_id', 0);
        $phase_id = \Request::get('phase_id', 0);
        $phase_status_id = \Request::get('phase_status_id', 0);
        $row_num = \Request::get('row_num', 0);

        $phase_status_date = $phase_status_id == 3 ? date('Y-m-d') : null;
        if (!$planPhaseId) { // add new phase
            $vals = compact('plan_id', 'phase_id', 'phase_status_id', 'row_num', 'phase_status_date');
            TreatmentPlanPhase::insert($vals);
        } else {
            TreatmentPlanPhase::where('id', $planPhaseId)->
                update(compact('phase_id', 'phase_status_id', 'phase_status_date'));
        }
        $this->planPhases = TreatmentPlanPhase::getPlanPhases($plan_id);
    }

    public function onPlanPhaseDelete($planId) {
        $planPhaseId = \Request::get('id', 0);
        TreatmentPlanPhase::removePhase($planPhaseId);
        $this->planPhases = TreatmentPlanPhase::getPlanPhases($planId);
    }

}
