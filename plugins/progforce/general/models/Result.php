<?php namespace Progforce\General\Models;

use Model;
use Progforce\General\Classes\Helpers\ResultHelper;

class Result extends Model {

    public $table = 'progforce_general_results';

    protected $guarded = ['*'];

    protected $fillable = [];

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

    public static function generate() {
        $result = new self();
        $result = ResultHelper::fill($result);

//        dd($result);

        return $result;
    }
}
