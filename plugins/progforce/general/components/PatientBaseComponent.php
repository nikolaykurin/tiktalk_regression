<?php namespace Progforce\General\Components;

use Cms\Classes\ComponentBase;
use Progforce\General\Models\Country;
use Progforce\General\Models\LanguageConfiguration;
use Progforce\General\Models\RegisteredDevice;
use Progforce\User\Classes\SLPAuth;
use Progforce\User\Models\User;
use System\Models\File;

class PatientBaseComponent extends ComponentBase {

    public function componentDetails() {
        return [
            'name'        => 'Clinician Patients Component',
            'description' => 'No description provided yet...'
        ];
    }
    
    public function onModifyPatient() {
        $this->page['user'] = SLPAuth::check();
        $patientId = \Request::get('patientId', 0);
        $this->page['patient'] = $patientId == 0 ? null : User::find($patientId);
        $this->setOptions();
        return ['#partialModifyPatient' =>  $this->renderPartial('modify-patient-modal')];
    }

    public function onUpdatePatient() {
        $request = \Request::all();

        $patientId = $request['id'];
        $patient = !$patientId ? new User() : User::find($patientId);
        if (!$patientId) {
            $patient->slp_id = $request['slp_id'];
        }
        $patient->first_name = $request['first_name'];
        $patient->last_name = $request['last_name'];
        $patient->birth_date = $request['birth_date'];
        $patient->country_id = $request['country_id'];
        $patient->language_id = $request['language_id'];
        $patient->registered_device_id = (int) $request['registered_device_id'];
        if (!$patientId) {
            $patient->code = 0;
        }
        $patient->save();
        if (!$patientId) {
            $patient->code = $patient->id; //TO-DO replace code with ID???
            $patient->save();
        }

        if (array_key_exists('avatar', $request)) {
            $file = new File();
            $file->data = $request['avatar'];
            $file->is_public = true;
            $file->save();
            $patient->avatar()->add($file);
        }
    }

    private function setOptions() {
        $this->page['languages'] = LanguageConfiguration::all()->pluck('language', 'id')->toArray();
        $this->page['countries'] = Country::get()->pluck('description', 'id')->toArray();
        $this->page['devices'] = ['0' => "<None>"] + RegisteredDevice::getList();
    }
}