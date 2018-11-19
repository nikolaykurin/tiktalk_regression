<?php namespace Progforce\User\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Redirect;
use Progforce\User\Classes\SLPAuth;
use Request;
use Flash;
use Lang;

class SLPSession extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'SLPSession Component',
            'description' => 'Check auth for SLP users'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => 'progforce.user::lang.session.redirect_title',
                'description' => 'progforce.user::lang.session.redirect_desc',
                'type'        => 'dropdown',
                'default'     => ''
            ]
        ];
    }

    public function getRedirectOptions() {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun() {
        if (!$user = $this->checkUserAuth()) {
            $redirectUrl = $this->controller->pageUrl($this->property('redirect'));
            return Redirect::guest($redirectUrl);
        }

        $this->page['user'] = $user;
    }

    public function checkUserAuth() {
        if($user = SLPAuth::check()){
            return $user;
        }

        return false;
    }

    public function onLogout() {
//        $user = $this->checkUserAuth();

        SLPAuth::logout();

        $url = post('redirect', Request::fullUrl());

        //Flash::success(Lang::get('progforce.user::lang.session.logout'));

        return Redirect::to($url);
    }
}
