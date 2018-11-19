<?php

namespace Progforce\General\Classes\Helpers;

use DB;
use Illuminate\Support\Facades\Config;
use Progforce\General\Models\Log;
use Progforce\General\Models\PatientTreatmentPlan;
use Progforce\General\Models\TreatmentPlanPhase;

class PatientTreatmentPlanHelper
{

    public static $BENCHMARKING_TUNING = 16;
    public static $BENCHMARKING = 17;

    public $userID;
    public $plan;
    public $phase = null;
    public $nextPhase = null;

    /**
     * PatientTreatmentPlanHelper constructor.
     * @param $userID
     */
    public function __construct($userID) {
        $this->userID = $userID;
        $this->plan = PatientTreatmentPlan::getFirstForUser($this->userID);
    }

    /**
     * @param int $statusValue
     * @return mixed
     */
    public function updatePlan($statusValue = 3) {
        $logActions = Config::get('log.actions');
        $isSet = $this->setCurrentTreatmentPhase($this->plan);
        if ($isSet) {
            $vals = ['phase_status_id' => $statusValue];
            if ($statusValue == 3) {
                $vals['phase_status_date'] = date('Y-m-d');
            }
            TreatmentPlanPhase::
                    where('id', $this->phase->plan_phase_id)->
                    update($vals);

            // complete status
            if ($statusValue === 3 && $this->nextPhase && $this->nextPhase->phase_status_id) {
                TreatmentPlanPhase::
                    where('id', $this->nextPhase->plan_phase_id)->
                    update(['phase_status_id' => 2]);
            }
            $this->plan->protocol_status = 2;
            $this->plan->save();

            // Marks AM acquisition for all user's plans to complete status
            if ($this->phase->id == 1 && $statusValue == 3) {
                $plans = PatientTreatmentPlan::getAllByUser($this->userID)->toArray();
                $planIds = array_column($plans, 'id');
                TreatmentPlanPhase::whereIn('plan_id', $planIds)->
                    where('phase_id', 1)->
                    update([
                        'phase_status_id' => $statusValue,
                        'phase_status_date' => date('Y-m-d')
                    ]);
            }
        }

        if ($isSet) {
            $isSet = $this->setCurrentTreatmentPhase($this->plan);
            Log::create([
                'user_id' => $this->userID,
                'datetime' => date('Y-m-d H:i:s'),
                'action' => $logActions['phase_change'],
                'data' => json_encode([
                    'plan' => $this->plan->toArray(),
                    'phase' => (array) $this->phase,
                    'status' => $this->phase->id,
                    'next_phase' => $this->nextPhase ? (array) $this->nextPhase : null,
                    'next_phase_status' => $this->nextPhase ? $this->nextPhase->phase_status_id : null
                ])
            ]);
        }
        if ($this->plan && !$isSet) {
            $this->plan->protocol_status = 3;
            $this->plan->save();
            $this->plan = PatientTreatmentPlan::getFirstForUser($this->userID);
        }
        return $this->plan;
    }

    /**
     * @param $plan
     * @return bool|null
     */
    public function setCurrentTreatmentPhase($plan) {
        if (!$plan) { return false; }
        $phases = 
            DB::table('progforce_general_treatment_phases as p')->
            select('p.*', 'pp.plan_id', 'pp.phase_id', 'pp.phase_status_id', 'pp.id as plan_phase_id')->
            leftJoin('progforce_general_treatment_plans_phases as pp', 'pp.phase_id', 'p.id')->
            where('pp.plan_id', $plan->id)->
            orderBy('pp.row_num')->
            get();

        foreach ($phases as $key => $phase) {
            if (($phase->phase_status_id == 1 || $phase->phase_status_id == 2)) {
                $this->phase = $phase;
                if (isset($phases[$key+1])) {
                    $this->nextPhase = $phases[$key+1];
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getCurrentTreatmentPhase() {
        $this->setCurrentTreatmentPhase($this->plan);
        return $this->phase;
    }

    public function getSoundsByUserFor1stPhase() {
        $sounds = PatientTreatmentPlan::getAllByUser($this->userID);
        $soundIds = [];
        foreach ($sounds as $value) {
            $soundIds[] = $value->sound_id;
        }
        return $soundIds;
    }
}