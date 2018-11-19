<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use Progforce\User\Models\User;
use Progforce\General\Models\Sound;

class PatientTreatmentPlan extends Model
{
    use Validation;

    public $table = 'progforce_general_patient_treatment_plans';

    public $rules = [
        'user_id' => 'required',
        'protocol_sequence' => 'required|numeric|integer',
        'protocol_status' => 'required',
   //     'sound' => 'required'
    ];

    protected $fillable = [
        'user_id',
        'protocol_sequence',
        'protocol_status',
    ];

    public $belongsToMany = [
        'phases' => [
            TreatmentPhase::class,
            'table' => 'progforce_general_treatment_plans_phases',
            'key' => 'plan_id',
            'otherKey' => 'phase_id',
            'pivot' => ['row_num', 'phase_status_id', 'phase_status_date'],
        ],
    ];   

    
    public $belongsTo = [
        'user' => User::class,
        'sound' => Sound::class,
        //
        'protocol_status_field' => [
            TreatmentStatus::class,
            'key' => 'protocol_status'
        ],
    ];

    private $is_replicate_mode = false;

    public function setReplicateMode() {
        $this->is_replicate_mode = true;
    }

    // Frist plan phase_id should be always == 1 if not replicate mode
    public function afterCreate() {
        if (!$this->is_replicate_mode) {
            $planPhase = new TreatmentPlanPhase();
            $planPhase->plan_id = $this->id;
            $planPhase->phase_id = 1;
            $planPhase->row_num = 1;
            $planPhase->save();
        }
    }

    public function getSoundOptions() {
        $langId = $this->user ? $this->user->language_id : 1;
        $sounds = Sound::where('language_id', $langId)->get()->toArray();
        return array_column($sounds, 'sound', 'id');
    }

    public static function getSounds($userId) {
        return self::from('progforce_general_patient_treatment_plans as p')->
            select('p.id as plan_id', 's.id as s.sound_id', 's.sound')->
            leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'sound_id')->
            where('user_id', $userId)->
            get();
    }

    private static function getByUserQry($userId) {
        return self::from('progforce_general_patient_treatment_plans as p')->
            select('p.id', 'p.user_id', 'p.protocol_sequence', 'p.sound_id', 's.sound', 'p.protocol_status')->
            leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'sound_id')->
            whereIn('p.protocol_status', [1,2])->
            where('p.user_id', $userId);
    }

    public static function getFirstForUser($userId) {
        return self::getByUserQry($userId)->first();
    }

    public static function getAllByUser($userId) {
        return self::getByUserQry($userId)->orderBy('protocol_sequence')->get();
    }

    public function beforeSave() {
        $max = 0;
        if($this->exists && (int)$this->protocol_sequence == $this->getOriginal()['protocol_sequence'])
            $max = 1;

        $invalid = $this->newQuery()
                ->where('protocol_sequence', $this->protocol_sequence)
                ->where('user_id', $this->user_id)
                ->count() > $max;

        if ($invalid) {
            throw new \ValidationException(['protocol_sequence' =>
                'Protocol Sequence should be unique per user']);
        }

  //  if ($this->sound !== 'any') {
   //         return;
  //      }
    }
}