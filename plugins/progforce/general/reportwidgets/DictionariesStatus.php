<?php namespace Progforce\General\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

class DictionariesStatus extends ReportWidgetBase {

    protected function loadAssets() {
        $this->addCss('css/widget.css');
    }

    public function render() {
        $this->vars['test'] = 'TEST';

        return $this->makePartial('widget');
    }

}
