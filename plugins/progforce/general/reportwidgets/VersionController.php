<?php namespace Progforce\General\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Progforce\General\Classes\Helpers\PathHelper;
use October\Rain\Exception\ValidationException;

class VersionController extends ReportWidgetBase
{

    protected function loadAssets() {
        $this->addCss('css/widget.css');
    }

    public function render() {
        $infoFile = sprintf('%s/%s', PathHelper::getFilesPath(), Config::get('constants.files_info'));

        $content = file_exists($infoFile) ? json_decode(file_get_contents($infoFile), true) : [];
        $this->vars['files'] = $content;

        return $this->makePartial('widget');
    }

    public function onVersionChange() {
        $version = Input::get('version');
        $filename = Input::get('filename');

        $dlcPath = PathHelper::getFilesPath();
        $infoFile = sprintf('%s/%s', $dlcPath, Config::get('constants.files_info'));

        $content = file_exists($infoFile) ? json_decode(file_get_contents($infoFile), true) : [];
        $content[$filename]['version'] = $version;

        $fp = fopen($infoFile, 'w');
        fwrite($fp, json_encode($content));
        fclose($fp);

        return Redirect::to('/');
    }

}
