<?php namespace Progforce\General\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Complexities Back-end Controller
 */
class Complexities extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Progforce.General', 'general', 'complexities');
    }
}
