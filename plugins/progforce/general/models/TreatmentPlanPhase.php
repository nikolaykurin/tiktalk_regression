<?php namespace Progforce\General\Models;

use Model;
use Illuminate\Support\Facades\DB;
use October\Rain\Support\Collection;
use stdClass;

/**
 * PageProduct Model
 */
class TreatmentPlanPhase extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'progforce_general_treatment_plans_phases';
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
    public $belongsTo = [
        'phase' => TreatmentPhase::class,
        'plan' => PatientTreatmentPlan::class
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
    public $timestamps = false;

    public static function getPhasesByPlan($planId) {
        return DB::table('progforce_general_treatment_plans_phases as pp')->
            select(
                'pp.id',
                'pp.row_num',
                'pp.phase_id',
                'pp.plan_id',
                'pp.phase_status_id',
                'pp.phase_status_date',
                'p.description as phase_description',
                's.description as status_description'
            )->
            where('plan_id', $planId)->
            leftJoin('progforce_general_treatment_phases as p', 'p.id', '=', 'pp.phase_id')->
            leftJoin('progforce_general_treatment_statuses as s', 's.id', '=', 'pp.phase_status_id')->
            orderBy('row_num')->
            get();
    }

    public static function getPlanPhases($planId) {
        $userId = PatientTreatmentPlan::find($planId)->user_id;

        $phases = self::getPhasesByPlan($planId);

        return self::modifyPlanStatuses($phases, $userId);
    }

    /**
     * @param Collection $phases
     * @param integer $userId
     * @return mixed
     */
    private static function modifyPlanStatuses(Collection $phases, int $userId) {
        $changeablePhase = self::getChangeablePhaseByUser($userId);

        $phases = $phases->map(function (stdClass $phase) use ($changeablePhase) {
            $phase->changeable = $phase->phase_status_id === 3 || $phase->id === $changeablePhase->id;

            return $phase;
        });

        return $phases;
    }

    public static function getPlanPhase($id) {
        $phase = TreatmentPlanPhase::from('progforce_general_treatment_plans_phases as pp')->
            select(
                'pp.id',
                'pp.row_num',
                'pp.plan_id',
                'pp.phase_id',
                'pp.phase_status_id',
                'pp.phase_status_date',
                'ph.description as phase_description',
                's.sound',
                'st.description as status_description'
            )->
            where('pp.id', $id)->
            leftJoin('progforce_general_treatment_phases as ph', 'ph.id', '=', 'pp.phase_id')->
            leftJoin('progforce_general_treatment_statuses as st', 'st.id', '=', 'pp.phase_status_id')->
            leftJoin('progforce_general_patient_treatment_plans as p', 'p.id', '=', 'pp.plan_id')->
            leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'p.sound_id')->
            get()->
            first();
        return $phase;
    }

    public static function removePhase($id) {
        self::where('id', $id)->delete();
    }

    public static function getChangeablePhaseByUser($userId) {
        return self::getPhasesByPlan(PatientTreatmentPlan::getFirstForUser($userId)->id)
            ->whereIn('phase_status_id', [ 1, 2 ])
            ->first();
    }
}
