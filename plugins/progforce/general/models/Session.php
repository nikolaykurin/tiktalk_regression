<?php namespace Progforce\General\Models;

use Model;
use Progforce\User\Models\User;

class Session extends Model {

    public $table = 'progforce_general_sessions';

    protected $dates = [
        'datetime_start',
        'datetime_end'
    ];

    protected $guarded = ['*'];

    protected $fillable = [
        'user_id',
        'datetime_start',
        'datetime_end'
    ];

    public $hasOne = [];
    public $hasMany = [
        'logs' => [
            Log::class,
            'order' => 'datetime desc',
            'scope' => 'isOld'
        ]
    ];
    public $belongsTo = [
        'user' => User::class
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public static $TTL = 60 * 15;
}
