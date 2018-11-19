<?php namespace Progforce\General\Models;

use Model;

/**
 * ProgforceGeneralSound Model
 */
class Sound extends Model
{

    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'progforce_general_treatment_sounds';

    public $timestamps = false;

    public $rules = [
        'sound' => 'required|uniqsound',
    ];

    public $customMessages = [
        'sound.uniqsound' => 'Sound already exists!',
    ];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['language_id', 'sound'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public static function getSoundId($langId, $val) {
        $res = 0;
        $soundVal = strtolower(trim($val));
        if ($langId && $soundVal) {
            $sound = Sound::where(
                ['language_id' => $langId, 'sound' => $soundVal]
            )->first();
            if (!$sound) {traceLog($soundVal);
                $sound = new Sound();
                $sound->language_id = $langId;
                $sound->sound = $soundVal;
                $sound->save();
            }
            $res = $sound->id;
        }
        return $res;
    }

    public function getLanguageAttribute() {
        $lang = array_get(\Config::get('languages'), $this->language_id);
        return $lang ? $lang['code'] : '';
    }

    public function getLanguageIdOptions($keyValue = null) {
        return array_column(\Config::get('languages'), 'code', 'id');
    }

}
