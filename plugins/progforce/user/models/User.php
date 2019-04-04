<?php namespace Progforce\User\Models;

use Config;
use Illuminate\Contracts\Auth\Authenticatable;
use October\Rain\Exception\ValidationException;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Models\Country;
use Progforce\General\Models\LanguageConfiguration;
use Progforce\General\Models\PatientParent;
use Progforce\General\Models\PatientTreatmentPlan;
use Progforce\General\Models\RegisteredDevice;
use DateTime;
use Carbon\Carbon;
use Progforce\General\Models\Session;
use Str;
use Auth;
use Mail;
use Event;
use October\Rain\Auth\Models\User as UserBase;
use Progforce\User\Models\Settings as UserSettings;

class User extends UserBase implements Authenticatable
{
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    protected $table = 'users';

    /**
     * Validation rules
     */
    public $rules = [
        'first_name'   => 'required',
        'country' => 'required|numeric|integer',
        'language' => 'required|numeric|integer',
    //    'avatar'   => 'nullable|image|max:4000',
    ];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'groups' => [
            UserGroup::class,
            'table' => 'users_groups'
        ]
    ];

    public $belongsTo = [
        'country' => Country::class,
        'language' => LanguageConfiguration::class,
        'slp' => SLP::class,
        'registered_device' => RegisteredDevice::class,
        'parent' => PatientParent::class
    ];

    public $attachOne = [
        'avatar' => \System\Models\File::class
    ];

    public $hasMany = [
        'patient_treatment_plan' => PatientTreatmentPlan::class,
        'sessions' => Session::class
    ];

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'is_activated',
        'activated_at',
        'first_name',
        'last_name',
        'slp_id',
        'country_id',
        'language_id'
    ];

    /**
     * Purge attributes from data set.
     */
    protected $purgeable = ['password_confirmation', 'send_invite'];

    protected $dates = [
        'last_seen',
        'deleted_at',
        'created_at',
        'updated_at',
        'activated_at',
        'last_login'
    ];

    public static $loginAttribute = null;


    public function getRegisteredDeviceText() {
        $res = '';
        if ($this->registered_device) {
            if (!$this->registered_device->id) { return null; }
            $codes = ['imei', 'android', 'mac', 'firebase', 'unity'];
            foreach ($codes as $code) {
                $key =  $code . '_id';
                if ($this->registered_device->$key) {
                    $res = $this->registered_device->$key . ' (' . $code . ')';
                    break;
                 }
            }
        }
        return $res;
    }

    public function getRegisteredDeviceIdAttribute() {
        $res = null;
        $deviceId = array_get($this->attributes, 'registered_device_id', 0);
        if ($deviceId) {
            $device = RegisteredDevice::find($deviceId);
            if (!$device) { return null; }
            $codes = Config::get('tiktalk.device_codes');
            foreach ($codes as $code) {
                $key =  $code . '_id';
                if ($device->$key) {
                    $res = $device->id . '_' . $code;
                    break;
                 }
            }
        }
        return $res;
    }

    public function getRegisteredDeviceIdOptions($keyValue = null) {
        return RegisteredDevice::getList();
    }

    public function beforeSave() {
        if (key_exists('registered_device_id', $this->attributes)) {
            $this->attributes['registered_device_id'] = (int) $this->attributes['registered_device_id'];
        }
    }

    // TO-DO remove age field at all
    public function beforeCreate() {
        $this->age = 0;
        $this->slp_first_name = '';
    }

    public function getAgeCalcAttribute() {
        $res = null;
        if ($this->birth_date) {
            $date1 = new DateTime($this->birth_date);
            $date2 = new DateTime(date("Y-m-d"));
            $res = date_diff($date1, $date2)->y;
        }
        return $res;
    }
    
    public function getAuthIdentifierName() {
        return 'id';
    }

    /**
     * Sends the confirmation email to a user, after activating.
     * @param  string $code
     * @return bool
     */
    public function attemptActivation($code)
    {
        $result = parent::attemptActivation($code);
        if ($result === false) {
            return false;
        }

        Event::fire('progforce.user.activate', [$this]);

        return true;
    }

    /**
     * Converts a guest user to a registered one and sends an invitation notification.
     * @return void
     */
    public function convertToRegistered($sendNotification = true)
    {
        // Already a registered user
        if (!$this->is_guest) {
            return;
        }

        if ($sendNotification) {
            $this->generatePassword();
        }

        $this->is_guest = false;
        $this->save();

        if ($sendNotification) {
            $this->sendInvitation();
        }
    }

    //
    // Constructors
    //

    /**
     * Looks up a user by their email address.
     * @return self
     */
    public static function findByEmail($email)
    {
        if (!$email) {
            return;
        }

        return self::where('email', $email)->first();
    }

    //
    // Getters
    //

    /**
     * Gets a code for when the user is persisted to a cookie or session which identifies the user.
     * @return string
     */
    public function getPersistCode()
    {
        $block = UserSettings::get('block_persistence', false);

        if ($block || !$this->persist_code) {
            return parent::getPersistCode();
        }

        return $this->persist_code;
    }

    /**
     * Returns the public image file path to this user's avatar.
     */
    public function getAvatarThumb($size = 25, $options = null)
    {
        if (is_string($options)) {
            $options = ['default' => $options];
        }
        elseif (!is_array($options)) {
            $options = [];
        }

        // Default is "mm" (Mystery man)
        $default = array_get($options, 'default', 'mm');

        if ($this->avatar) {
            return $this->avatar->getThumb($size, $size, $options);
        }
        else {
            return '//www.gravatar.com/avatar/'.
                md5(strtolower(trim($this->email))).
                '?s='.$size.
                '&d='.urlencode($default);
        }
    }

    /**
     * Returns the name for the user's login.
     * @return string
     */
    public function getLoginName()
    {
        if (static::$loginAttribute !== null) {
            return static::$loginAttribute;
        }

        return static::$loginAttribute = UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
    }

    
    public function getLastSessionDuration() {
        $last_login = $this->last_login ? Carbon::createFromFormat('Y-m-d H:i:s', $this->last_login) : null;
        $last_seen = $this->last_seen ? Carbon::createFromFormat('Y-m-d H:i:s', $this->last_seen) : null;

        if (!$last_login || !$last_seen) {
            return null;
        }

        if ($last_seen->lessThan($last_login)) {
            return -1;
        }

        $lastSessionDuration = $last_seen->diffInMinutes($last_login);

        return $lastSessionDuration > 0 ? $lastSessionDuration : 1;
    }
    
    
    //
    // Scopes
    //

    public function scopeIsActivated($query)
    {
        return $query->where('is_activated', 1);
    }

    public function scopeFilterByGroup($query, $filter)
    {
        return $query->whereHas('groups', function($group) use ($filter) {
            $group->whereIn('id', $filter);
        });
    }

    //
    // Events
    //

    /**
     * Before validation event
     * @return void
     */
    public function beforeValidate()
    {
        /*
         * Guests are special
         */
        if ($this->is_guest && !$this->password) {
            $this->generatePassword();
        }

        if(empty($this->slp)) {
            $this->slp_id = 0;
        }

        $this->is_activated = 1;
    }

    public function beforeUpdate() {
        if (
            $this->id === (int) Config::get('users.guest_id')
            &&
            (
                $this->attributes['first_name'] !== $this->original['first_name']
                ||
                $this->attributes['last_name'] !== $this->original['last_name']
            )
        ) {
            throw new ValidationException(['id' => 'You can\'t edit guest\'s name!']);
        }
    }

    /**
     * After create event
     * @return void
     */
    public function afterCreate()
    {
        $this->restorePurgedValues();

//        if ($this->send_invite) {
//            $this->sendInvitation();
//        }
    }

    /**
     * After login event
     * @return void
     */
    public function afterLogin()
    {
        $this->last_login = $this->last_seen = $this->freshTimestamp();

        if ($this->trashed()) {
            $this->restore();

            Mail::sendTo($this, 'progforce.user::mail.reactivate', [
                'name' => $this->name
            ]);

            Event::fire('progforce.user.reactivate', [$this]);
        }
        else {
            parent::afterLogin();
        }

        Event::fire('progforce.user.login', [$this]);
    }

    /**
     * After delete event
     * @return void
     */
    public function afterDelete()
    {
        if ($this->isSoftDelete()) {
            Event::fire('progforce.user.deactivate', [$this]);
            return;
        }

        $this->avatar && $this->avatar->delete();

        parent::afterDelete();
    }

    //
    // Banning
    //

    /**
     * Ban this user, preventing them from signing in.
     * @return void
     */
    public function ban()
    {
        Auth::findThrottleByUserId($this->id)->ban();
    }

    /**
     * Remove the ban on this user.
     * @return void
     */
    public function unban()
    {
        Auth::findThrottleByUserId($this->id)->unban();
    }

    /**
     * Check if the user is banned.
     * @return bool
     */
    public function isBanned()
    {
        $throttle = Auth::createThrottleModel()->where('user_id', $this->id)->first();
        return $throttle ? $throttle->is_banned : false;
    }

    //
    // Last Seen
    //

    /**
     * Checks if the user has been seen in the last 5 minutes, and if not,
     * updates the last_seen timestamp to reflect their online status.
     * @return void
     */
    public function touchLastSeen()
    {
        if ($this->isOnline()) {
            return;
        }

        $oldTimestamps = $this->timestamps;
        $this->timestamps = false;

        $this
            ->newQuery()
            ->where('id', $this->id)
            ->update(['last_seen' => $this->freshTimestamp()])
        ;

        $this->last_seen = $this->freshTimestamp();
        $this->timestamps = $oldTimestamps;
    }

    /**
     * Returns true if the user has been active within the last 5 minutes.
     * @return bool
     */
    public function isOnline()
    {
        return $this->getLastSeen() > $this->freshTimestamp()->subMinutes(5);
    }

    /**
     * Returns the date this user was last seen.
     * @return Carbon\Carbon
     */
    public function getLastSeen()
    {
        return $this->last_seen ?: $this->created_at;
    }

    //
    // Utils
    //

    /**
     * Returns the variables available when sending a user notification.
     * @return array
     */
    public function getNotificationVars()
    {
        $vars = [
            'name'     => $this->name,
            'email'    => $this->email,
            'username' => $this->username,
            'login'    => $this->getLogin(),
            'password' => $this->getOriginalHashValue('password')
        ];

        /*
         * Extensibility
         */
        $result = Event::fire('progforce.user.getNotificationVars', [$this]);
        if ($result && is_array($result)) {
            $vars = call_user_func_array('array_merge', $result) + $vars;
        }

        return $vars;
    }

    /**
     * Sends an invitation to the user using template "progforce.user::mail.invite".
     * @return void
     */
    protected function sendInvitation()
    {
        Mail::sendTo($this, 'progforce.user::mail.invite', $this->getNotificationVars());
    }

    /**
     * Assigns this user with a random password.
     * @return void
     */
    protected function generatePassword()
    {
        $this->password = $this->password_confirmation = Str::random(6);
    }

    public static function getGuest() {
        return self::find(Config::get('users.guest_id'));
    }

    public function isGuest() {
        return $this->id === (int) Config::get('users.guest_id');
    }

    public static function getByCode($code) {
        return self::where('code', $code)->first();
    }

    public function hasModel() {
        if (!$this->id) {
            return false;
        }

        return !PathHelper::checkDirectoryEmpty(PathHelper::getUserAbsoluteModelPathForLanguage($this->id));
    }

    public function hasAMRecordings() {
        return !empty(glob(PathHelper::getUserAbsoluteRecordingsPath($this->id) . '/' . 'AM_*'));
    }

    public function hasTrainingRecordings() {
        return !empty(glob(PathHelper::getUserAbsoluteRecordingsPath($this->id) . '/' . 'REC_*'));
    }
}
