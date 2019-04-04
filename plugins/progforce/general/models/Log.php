<?php namespace Progforce\General\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Model;
use Progforce\User\Models\User;

class Log extends Model
{
    public $table = 'progforce_general_logs';

    protected $dates = [
        'datetime'
    ];

    protected $fillable = [
        'user_id',
        'session_id',
        'datetime',
        'action',
        'data'
    ];

    public $belongsTo = [
        'user' => [
            User::class
        ],
        'session' => [
            Session::class
        ]
    ];

    public function scopeIsOld($query) {
        return $query->where('created_at', '<=', Carbon::now()->subSeconds(Session::$TTL)->format('Y-m-d H:i:s'));
    }

    public function getActionOptions() {
        return array_flip(Config::get('log.actions'));
    }

    public function getActionAttribute() {
        return array_flip(Config::get('log.actions'))[$this->attributes['action']];
    }
}
