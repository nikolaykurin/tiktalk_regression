<?php namespace Progforce\General\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Progforce\General\Classes\Helpers\PathHelper;

class AssetsParser extends ReportWidgetBase
{

    protected function loadAssets() {
        $this->addCss('css/widget.css');
    }

    public function render() {
        $filesPath = PathHelper::getFilesPath();

        $this->vars['has_en'] = file_exists(sprintf('%s/%s', $filesPath, Config::get('constants.assets_parser.en')));
        $this->vars['has_he'] = file_exists(sprintf('%s/%s', $filesPath, Config::get('constants.assets_parser.he')));

        return $this->makePartial('widget');
    }

}
