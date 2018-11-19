<?php namespace Progforce\General\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\Helpers\PatientTreatmentPlanHelper;
use Progforce\General\Models\SLPProfile;
use Progforce\User\Controllers\RoutesControllers\Account;
use Progforce\User\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class SLP extends ComponentBase {

    public function componentDetails() {
        return [
            'name'        => 'Clinician SLP Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties() {
        return [];
    }

    public function onRun() {
        //
    }

}