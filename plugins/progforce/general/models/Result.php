<?php namespace Progforce\General\Models;

use Model;
use Progforce\General\Classes\Helpers\ResultHelper;

class Result extends Model {

    public $table = 'progforce_general_results';

    protected $guarded = ['*'];

    protected $fillable = [
        'treatment_started_at',
        'treatment_finished_at',
        'treatment_duration',
        'treatment_complexity',
        'treatment_phases_count',
        'patient_age',
        'patient_gender',
        'is_real'
    ];

    public $timestamps = false;

    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getPatientGenderValueAttribute() {
        return in_array($this->patient_gender, array_keys(ResultHelper::$GENDERS)) ? ucfirst(ResultHelper::$GENDERS[$this->patient_gender]) : 'N/A';
    }

    public static function generate() {
        $result = new self();
        $result = ResultHelper::fill($result);

        return $result;
    }

}
