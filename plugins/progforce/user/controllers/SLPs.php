<?php namespace Progforce\User\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * S L Ps Back-end Controller
 */
class SLPs extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Progforce.User', 'slps', 'slps');
    }

    public function onSave() {
        //
    }
}
