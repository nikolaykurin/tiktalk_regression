<?php namespace Progforce\User\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Progforce\User\Classes\SLPAuth;
use Redirect;
use Validator;
use ValidationException;
use Exception;
use Request;
use Flash;

class SLPAccount extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'SLP Account Component',
            'description' => 'SLP`s account and log in component'
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
        ];
    }

    public function getRedirectOptions()
    {
        return [''=>'- refresh page -', '0' => '- no redirect -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onSignin() {
        try {
            /*
             * Validate input
             */
            $data = post();
            $rules = [];

            $rules['login'] = 'required';
            $rules['password'] = 'required';

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

            SLPAuth::authenticate($credentials, true);

            /*
             * Redirect
             */
            $redirectUrl = $this->controller->pageUrl($this->property('redirect'));
            return Redirect::guest($redirectUrl);

        } catch (Exception $ex) {
            return ['#partialError' => $this->renderPartial('login-error', ['message' => $ex->getMessage()])];
        }
    }
}
