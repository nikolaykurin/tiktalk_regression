<?php namespace Progforce\General\Models;

use Model;

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
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
    public $timestamps = false;

    public static function getPlanPhases($planId) {
        $phases = TreatmentPlanPhase::from('progforce_general_treatment_plans_phases as pp')->
            select(
                'pp.id',
                'pp.row_num',
                'pp.phase_id',
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
}
