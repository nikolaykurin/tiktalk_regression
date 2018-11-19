<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class TreatmentStatus extends Model
{
    use Validation;

    public $table = 'progforce_general_treatment_statuses';

    public $timestamps = false;

    public $rules = [
        'description' => 'required'
    ];
}
