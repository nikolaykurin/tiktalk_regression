<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Country extends Model
{
    use Validation;

    public $table = 'progforce_general_countries';

    public $timestamps = false;

    public $rules = [
        'code' => 'required|numeric|integer',
        'description' => 'required'
    ];
}
