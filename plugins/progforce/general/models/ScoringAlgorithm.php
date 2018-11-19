<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class ScoringAlgorithm extends Model
{
    use Validation;

    public $table = 'progforce_general_scoring_algorithms';

    public $timestamps = false;

    public $rules = [
        'field' => 'required',
        'value' => 'required'
    ];
}
