<?php namespace Progforce\Report\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Config;
//use Progforce\General\Controllers\RoutesControllers\Config;

/**
 * Sounds Back-end Controller
 */
class Sounds extends Controller
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
        BackendMenu::setContext('Progforce.Report', 'report', 'sounds');
        $this->vars['list'] = array_column(Config::get('languages'), 'code', 'id');
    }

    public function onSoundReport() {
        return redirect('/report-sound-count?lang='.post('lang'));
    }

    public function onWordsReport() {
        return redirect('/report-words?langId='.post('lang') . '&type='.post('report_type'));
    }
}
