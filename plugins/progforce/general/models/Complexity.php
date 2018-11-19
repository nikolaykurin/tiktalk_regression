<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Complexity extends Model
{
    use Validation;

    public $table = 'progforce_general_complexities';

    public $timestamps = false;

    public $rules = [
        'description' => 'required'
    ];
}
