<?php namespace Progforce\General\Models;

use Model;
use Progforce\User\Models\User;
use Illuminate\Support\Facades\Hash;

// name "Parent" already reserved :(
class PatientParent extends Model {

    public $table = 'progforce_general_parents';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $fillable = [];

    public $rules = [
        'username' => 'required|unique:progforce_general_parents',
        'email' => 'required|unique:progforce_general_parents'
    ];

    public $hasOne = [];
    public $hasMany = [
        'children' => [ User::class, 'key' => 'parent_id' ]
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function beforeCreate() {
        $this->attributes['password'] = Hash::make($this->attributes['password']);
    }

    public function beforeUpdate() {
        $newPasswordHash = Hash::make($this->attributes['password']);
        $oldPasswordHash = $this->original['password'];

        if (!Hash::check($oldPasswordHash, $newPasswordHash)) {
            $this->attributes['password'] = $newPasswordHash;
        }
    }

}
