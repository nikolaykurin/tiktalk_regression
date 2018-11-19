<?php namespace Progforce\General\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Progforce\General\Classes\Helpers\PathHelper;

class Logs extends ReportWidgetBase {

    public static $SPHINX_LOGS_DIR = 'logdir';

    protected function loadAssets() {
        $this->addCss('css/widget.css');
    }

    public function render() {
        $path = PathHelper::getBaseTempPath();

        $langs = [];

        $search = glob($path . '/*');
        foreach ($search as $item) {
            if (is_dir($item) && is_dir($item . DIRECTORY_SEPARATOR . self::$SPHINX_LOGS_DIR)) {
                $langs[]  = array_reverse(explode(DIRECTORY_SEPARATOR, $item))[0];
            }
        }

        $this->vars['langs'] = $langs;

        return $this->makePartial('widget');
    }

}