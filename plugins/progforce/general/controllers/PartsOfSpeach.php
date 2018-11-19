<?php namespace Progforce\General\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class PartsOfSpeach extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController','Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Progforce.General', 'general', 'side-menu-item-partsofspeach');
    }
}
