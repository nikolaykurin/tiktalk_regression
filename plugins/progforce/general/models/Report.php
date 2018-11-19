<?php namespace Progforce\General\Models;

use Model;

/**
 * Report Model
 */
class Report extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'progforce_general_reports';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
