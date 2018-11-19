<?php namespace Progforce\User\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Model;
use October\Rain\Database\Traits\Hashable;
use Progforce\General\Models\LanguageConfiguration;
use RuntimeException;

/**
 * SLP Model
 * @property string $password
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property integer $gender
 * @property string $address
 * @property string $languages
 * @property string $picture
 * @property string $user_name
 * @property string $persist_code
 *
 */
class SLP extends Model
{
    use \October\Rain\Database\Traits\Hashable;
//    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'progforce_user_s_l_p_s';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $hashable = ['persist_code', 'password'];

//    public $rules = [
//        'user_name' => 'required|between:3,64|user_name|unique:s_l_p_s',
//        'password' => 'required:create|between:2,32',
//    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'patients' => [
            User::class,
            'key' => 'slp_id'
        ]
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [
        'avatar' => 'System\Models\File'
    ];
    public $attachMany = [];

    protected $jsonable = ['languages'];

    public $timestamps = false;

    public function beforeSave() {
//        if(Hash::class)
//        $this->password = $this->original['password'];
//        print_r([$this->password, $this->original['password'], $this->attributes['password']]);
    }

    public function afterSave() {
//        Redirect::to();
//        $this->password = '';
    }

    public function beforeUpdate() {
//        print_r([$this->password, $this->original['password'], $this->attributes['password']]);
//        $this->password = '';

    }

    public function getLanguagesOptions() {
        $langs = LanguageConfiguration::select(['id', 'language'])->get()->toArray();
        $languages = [];
        foreach($langs as $lang) {
            $languages[$lang['id']] = $lang['language'];
        }
        return $languages;
    }

    public function getLanguagesListAttribute() {
        $langs = LanguageConfiguration::select(['language'])
            ->whereIn('id', $this->languages)->get()->toArray();
        $languages = '';
        foreach ($langs as $key => $lang) {
            $languages .= $lang['language'] . ((count($langs) > $key+1) ? ', ' : '');
        }
        return $languages;
    }

    public function getLanguagesList() {
        return LanguageConfiguration::select(['language'])
            ->whereIn('id', $this->languages)->get()->toArray();
    }

    public function getPersistCode() {
        $this->password = '';
        $this->persist_code = $this->getRandomString();
        // Our code got hashed
        $persistCode = $this->persist_code;

        $this->save();

        return $persistCode;
    }

    public function getRandomString($length = 42) {
        /*
         * Use OpenSSL (if available)
         */
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false) {
                throw new RuntimeException('Unable to generate a random string');
            }

            return substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $length);
        }

        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    public function checkPersistCode($persistCode) {
        if (!$persistCode || !$this->persist_code) {
            return false;
        }

        return $persistCode == $this->persist_code;
    }

    public function setPasswordAttribute($value) {
        if ($this->exists && empty($value)) {
            unset($this->attributes['password']);
        } else {
            $this->attributes['password'] = $value;

            // Password has changed, log out all users
            $this->attributes['persist_code'] = null;
        }
    }
}
