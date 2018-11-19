<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class LocationWithinWordAux extends Model
{
    use Validation;

    public $table = 'progforce_general_location_within_word_aux';

    public $timestamps = false;

    public $rules = [
        'description' => 'required'
    ];
}
