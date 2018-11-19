<?php namespace Progforce\General\Models;

use Illuminate\Support\Facades\Config;
use Model;
use October\Rain\Database\Traits\Validation;
use Progforce\User\Models\User;

class Log extends Model
{
    use Validation;

    public $table = 'progforce_general_logs';

    public $timestamps = false;

    public $rules = [ ];

    protected $fillable = [
        'user_id',
        'datetime',
        'action',
        'data'
    ];

    public $belongsTo = [
        'user' => [
            User::class
        ]
    ];

    public function getActionOptions() {
        return array_flip(Config::get('log.actions'));
    }

    public function getActionAttribute() {
        return array_flip(Config::get('log.actions'))[$this->attributes['action']];
    }
}
