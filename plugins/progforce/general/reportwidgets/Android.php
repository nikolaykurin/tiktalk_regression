<?php namespace Progforce\General\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Config;

class Android extends ReportWidgetBase
{

    protected function loadAssets() {
        $this->addCss('css/widget.css');
    }

    public function render() {
        $speechanalyzer = sprintf('%s/%s', base_path(), Config::get('constants.speechanalyzer_name'));
        $game = sprintf('%s/%s', base_path(), Config::get('constants.game_name'));

        $this->vars['has_speechanalyzer'] = file_exists($speechanalyzer);
        $this->vars['has_game'] = file_exists($game);

        return $this->makePartial('widget');
    }

}