<?php namespace Progforce\General\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

class PatientParents extends Controller {

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct() {
        parent::__construct();

        BackendMenu::setContext('Progforce.General', 'patientparents', 'patientparents');
    }
}
