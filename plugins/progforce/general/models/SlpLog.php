<?php namespace Progforce\General\Models;

use Model;
use Config;
use Progforce\User\Classes\SLPAuth;

/**
 * SlpLog Model
 */
class SlpLog extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'progforce_general_slp_logs';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public static function addPlanPhaseLog($actionCode, $planPhaseId) {
        $slp =  SLPAuth::check();
        if (!$slp) { return; }
        $planPhase = TreatmentPlanPhase::getPlanPhase($planPhaseId);
        $description = '';
        switch ($actionCode) {
            case 'added_phase':
                $description = sprintf(
                    'Sound - %s. Phase. id - %d', 
                    $planPhase->sound, $planPhase->phase_id
                );
                break;
            case 'removed_phase':
                $description = sprintf(
                    'Sound - %s. %s',
                    $planPhase->sound, $planPhase->phase_description
                );
                break;
            case 'changed_phase':
                $description = sprintf(
                    'Sound - %s. New Value - %s, %s',
                    $planPhase->sound, $planPhase->phase_description, $planPhase->status_description
                );
                break;
        }

        self::add($slp->id, $planPhase->plan_id, $actionCode, $description);
    }

    public static function addPlanLog($actionCode, $planId) {
        $slp =  SLPAuth::check();
        if (!$slp) { return; }
        $plan = PatientTreatmentPlan::from('progforce_general_patient_treatment_plans as p')->
            select('p.id', 's.sound', 'st.description')->
            leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'p.sound_id')->
            leftJoin('progforce_general_treatment_statuses as st', 'st.id', '=', 'p.protocol_status')->
            where('p.id', $planId)->
            first();
        $description = '';
        switch ($actionCode) {
            case 'added_plan':
                $description = sprintf(
                    'Sound - %s', 
                    $plan->sound
                );
                break;
            case 'changed_plan_status':
                $description = sprintf(
                    'New Status - %s',
                    $plan->description
                );
                break;
            case 'removed_plan':
                $description = sprintf(
                    'Sound - %s',
                    $plan->sound
                );
                break;
        }

        self::add($slp->id, $plan->id, $actionCode, $description);
    }

    private static function add($slpId, $planId, $actionCode, $description) {
        $slpLog = new SlpLog();
        $slpLog->slp_id = $slpId;
        $slpLog->plan_id = $planId;
        $slpLog->action_id = Config::get('log.slp_actions.' . $actionCode);
        $slpLog->description = $description;
        $slpLog->save();
    }

}
