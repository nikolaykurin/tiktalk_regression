<?php namespace Progforce\User\Components;

use Lang;
use Auth;
use Mail;
use Event;
use Flash;
use Input;
use Request;
use Redirect;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Progforce\User\Models\Settings as UserSettings;
use Exception;

/**
 * Account component
 *
 * Allows users to register, sign in and update their account. They can also
 * deactivate their account and resend the account verification email.
 */
class Account extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => /*Account*/'progforce.user::lang.account.account',
            'description' => /*User management form.*/'progforce.user::lang.account.account_desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => /*Redirect to*/'progforce.user::lang.account.redirect_to',
                'description' => /*Page name to redirect to after update, sign in or registration.*/'progforce.user::lang.account.redirect_to_desc',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'paramCode' => [
                'title'       => /*Activation Code Param*/'progforce.user::lang.account.code_param',
                'description' => /*The page URL parameter used for the registration activation code*/ 'progforce.user::lang.account.code_param_desc',
                'type'        => 'string',
                'default'     => 'code'
            ],
            'forceSecure' => [
                'title'       => /*Force secure protocol*/'progforce.user::lang.account.force_secure',
                'description' => /*Always redirect the URL with the HTTPS schema.*/'progforce.user::lang.account.force_secure_desc',
                'type'        => 'checkbox',
                'default'     => 0
            ],
        ];
    }

    public function getRedirectOptions()
    {
        return [''=>'- refresh page -', '0' => '- no redirect -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
        $this->page['user'] = $this->user();
        $this->page['canRegister'] = $this->canRegister();
        $this->page['loginAttribute'] = $this->loginAttribute();
        $this->page['loginAttributeLabel'] = $this->loginAttributeLabel();
    }

    /**
     * Executed when this component is bound to a page or layout.
     */
    public function onRun()
    {
        /*
         * Redirect to HTTPS checker
         */
        if ($redirect = $this->redirectForceSecure()) {
            return $redirect;
        }

        /*
         * Activation code supplied
         */
        $routeParameter = $this->property('paramCode');

        if ($activationCode = $this->param($routeParameter)) {
            $this->onActivate($activationCode);
        }

        $this->prepareVars();
    }

    /**
     * Returns the logged in user, if available
     */
    public function user()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }

    /**
     * Flag for allowing registration, pulled from UserSettings
     */
    public function canRegister()
    {
        return UserSettings::get('allow_registration', true);
    }

    /**
     * Returns the login model attribute.
     */
    public function loginAttribute()
    {
        return UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
    }

    /**
     * Returns the login label as a word.
     */
    public function loginAttributeLabel()
    {
        return Lang::get($this->loginAttribute() == UserSettings::LOGIN_EMAIL
            ? /*Email*/'progforce.user::lang.login.attribute_email'
            : /*Username*/'progforce.user::lang.login.attribute_username'
        );
    }

    /**
     * Sign in the user
     */
    public function onSignin()
    {
        try {
            /*
             * Validate input
             */
            $data = post();
            $rules = [];

            $rules['login'] = $this->loginAttribute() == UserSettings::LOGIN_USERNAME
                ? 'required|between:2,255'
                : 'required|email|between:6,255';

            $rules['password'] = 'required|between:4,255';

            if (!array_key_exists('login', $data)) {
                $data['login'] = post('username', post('email'));
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Authenticate user
             */
            $credentials = [
                'login'    => array_get($data, 'login'),
                'password' => array_get($data, 'password')
            ];

            Event::fire('progforce.user.beforeAuthenticate', [$this, $credentials]);

            $user = Auth::authenticate($credentials, true);
            if ($user->isBanned()) {
                Auth::logout();
                throw new AuthException(/*Sorry, this user is currently not activated. Please contact us for further assistance.*/'progforce.user::lang.account.banned');
            }

            /*
             * Redirect
             */
            if ($redirect = $this->makeRedirection(true)) {
                return $redirect;
            }
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    /**
     * Register the user
     */
    public function onRegister()
    {
        try {
            if (!$this->canRegister()) {
                throw new ApplicationException(Lang::get(/*Registrations are currently disabled.*/'progforce.user::lang.account.registration_disabled'));
            }

            /*
             * Validate input
             */
            $data = post();

            if (!array_key_exists('password_confirmation', $data)) {
                $data['password_confirmation'] = post('password');
            }

            $rules = [
                'email'    => 'required|email|between:6,255',
                'password' => 'required|between:4,255|confirmed'
            ];

            if ($this->loginAttribute() == UserSettings::LOGIN_USERNAME) {
                $rules['username'] = 'required|between:2,255';
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Register user
             */
            Event::fire('progforce.user.beforeRegister', [&$data]);

            $requireActivation = UserSettings::get('require_activation', true);
            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;
            $user = Auth::register($data, $automaticActivation);

            Event::fire('progforce.user.register', [$user, $data]);

            /*
             * Activation is by the user, send the email
             */
            if ($userActivation) {
                $this->sendActivationEmail($user);

                Flash::success(Lang::get(/*An activation email has been sent to your email address.*/'progforce.user::lang.account.activation_email_sent'));
            }

            /*
             * Automatically activated or not required, log the user in
             */
            if ($automaticActivation || !$requireActivation) {
                Auth::login($user);
            }

            /*
             * Redirect to the intended page after successful sign in
             */
            if ($redirect = $this->makeRedirection(true)) {
                return $redirect;
            }

        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    /**
     * Activate the user
     * @param  string $code Activation code
     */
    public function onActivate($code = null)
    {
        try {
            $code = post('code', $code);

            /*
             * Break up the code parts
             */
            $parts = explode('!', $code);
            if (count($parts) != 2) {
                throw new ValidationException(['code' => Lang::get(/*Invalid activation code supplied.*/'progforce.user::lang.account.invalid_activation_code')]);
            }

            list($userId, $code) = $parts;

            if (!strlen(trim($userId)) || !($user = Auth::findUserById($userId))) {
                throw new ApplicationException(Lang::get(/*A user was not found with the given credentials.*/'progforce.user::lang.account.invalid_user'));
            }

            if (!$user->attemptActivation($code)) {
                throw new ValidationException(['code' => Lang::get(/*Invalid activation code supplied.*/'progforce.user::lang.account.invalid_activation_code')]);
            }

            Flash::success(Lang::get(/*Successfully activated your account.*/'progforce.user::lang.account.success_activation'));

            /*
             * Sign in the user
             */
            Auth::login($user);

        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    /**
     * Update the user
     */
    public function onUpdate()
    {
        if (!$user = $this->user()) {
            return;
        }

        if (Input::hasFile('avatar')) {
            $user->avatar = Input::file('avatar');
        }

        $user->fill(post());
        $user->save();

        /*
         * Password has changed, reauthenticate the user
         */
        if (strlen(post('password'))) {
            Auth::login($user->reload(), true);
        }

        Flash::success(post('flash', Lang::get(/*Settings successfully saved!*/'progforce.user::lang.account.success_saved')));

        /*
         * Redirect
         */
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }

        $this->prepareVars();
    }

    /**
     * Deactivate user
     */
    public function onDeactivate()
    {
        if (!$user = $this->user()) {
            return;
        }

        if (!$user->checkHashValue('password', post('password'))) {
            throw new ValidationException(['password' => Lang::get('progforce.user::lang.account.invalid_deactivation_pass')]);
        }

        Auth::logout();
        $user->delete();

        Flash::success(post('flash', Lang::get(/*Successfully deactivated your account. Sorry to see you go!*/'progforce.user::lang.account.success_deactivation')));

        /*
         * Redirect
         */
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    /**
     * Trigger a subsequent activation email
     */
    public function onSendActivationEmail()
    {
        try {
            if (!$user = $this->user()) {
                throw new ApplicationException(Lang::get(/*You must be logged in first!*/'progforce.user::lang.account.login_first'));
            }

            if ($user->is_activated) {
                throw new ApplicationException(Lang::get(/*Your account is already activated!*/'progforce.user::lang.account.already_active'));
            }

            Flash::success(Lang::get(/*An activation email has been sent to your email address.*/'progforce.user::lang.account.activation_email_sent'));

            $this->sendActivationEmail($user);

        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }

        /*
         * Redirect
         */
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    /**
     * Sends the activation email to a user
     * @param  User $user
     * @return void
     */
    protected function sendActivationEmail($user)
    {
        $code = implode('!', [$user->id, $user->getActivationCode()]);
        $link = $this->currentPageUrl([
            $this->property('paramCode') => $code
        ]);

        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('progforce.user::mail.activate', $data, function($message) use ($user) {
            $message->to($user->email, $user->name);
        });
    }

    /**
     * Redirect to the intended page after successful update, sign in or registration.
     * The URL can come from the "redirect" property or the "redirect" postback value.
     * @return mixed
     */
    protected function makeRedirection($intended = false)
    {
        $method = $intended ? 'intended' : 'to';

        $property = $this->property('redirect');

        if (strlen($property) && !$property) {
            return;
        }

        $redirectUrl = $this->pageUrl($property) ?: $property;

        if ($redirectUrl = post('redirect', $redirectUrl)) {
            return Redirect::$method($redirectUrl);
        }
    }

    /**
     * Checks if the force secure property is enabled and if so
     * returns a redirect object.
     * @return mixed
     */
    protected function redirectForceSecure()
    {
        if (
            Request::secure() ||
            Request::ajax() ||
            !$this->property('forceSecure')
        ) {
            return;
        }

        return Redirect::secure(Request::path());
    }
}
