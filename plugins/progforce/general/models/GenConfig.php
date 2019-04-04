<?php namespace Progforce\General\Models;

use Model;

/**
 * GenConfig Model
 */
class GenConfig extends Model {

    use \October\Rain\Database\Traits\Validation;

    public $implement = [
        'System.Behaviors.SettingsModel',
    ];

    // A unique code
    public $settingsCode = 'progforce_general_config';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';


    public $rules = [];

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

    public static function get() {
        return GenConfig::where('item', '=', 'progforce_general_config')->first();
    }

    public static function getActivationCode() {
        $cfg = self::get();
        return $cfg ? $cfg->activation_code : '';
    }

    function utf8_strrev($str){
    preg_match_all('/./us', $str, $ar);
    return join('', array_reverse($ar[0]));
}
    
    public function getWords($code) {
        $res = [];
        $fld = 'gui_' . $code;
        $rows = explode("\r\n", $this->$fld);
        $lastKey = null;
        foreach ($rows as $row) {
            $vals = explode('=', $row);
            if (count($vals) > 1) {
                $res[$vals[0]] = $vals[1];
                $lastKey = $vals[0];
            } elseif ($lastKey && $vals[0]) {   // for multiline support
                $res[$lastKey] .= "\n" . $vals[0];
            }
        }

        // Dirty Fix! Reverse for Hebrew
        if ($code == 'he') {
            foreach ($res as &$row) {
                //$row = strrev($row);
                $ar = [];
                preg_match_all('/./us', $row, $ar);
                $row = join('', array_reverse($ar[0]));
            }
            unset($row);
        }

        return $res;
    }

    public function initSettingsData() {
        $this->gui_en = $this->getConfigWords('en');
        $this->gui_he = $this->getConfigWords('he');
    }

    private function getConfigWords($code) {
        $res = '';
        $words = \Config::get('parser.' . $code, '');
        foreach ($words as $key=>$word) {
            $res .= $key . '=' . $word . "\n";
        }
        return $res;
    }

}
