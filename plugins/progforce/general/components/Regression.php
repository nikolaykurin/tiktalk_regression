<?php namespace Progforce\General\Components;

use Cms\Classes\ComponentBase;
use Progforce\General\Classes\Helpers\ResultHelper;

class Regression extends ComponentBase {

    public function componentDetails() {
        return [
            'name'        => 'Regression Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties() {
        return [];
    }

    public function onRun() {
        $this->page['gender_values'] = ResultHelper::$GENDERS;
        $this->page['complexity_values'] = ResultHelper::$COMPLEXITIES;
        $this->page['model_exist'] = file_exists(ResultHelper::getModelPath());
    }
}
