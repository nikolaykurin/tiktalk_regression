<?php namespace Progforce\General;

use Illuminate\Support\Facades\Validator;
use Progforce\General\Classes\GoogleDriveServiceProvider;
use Progforce\General\Components\Patient;
use Progforce\General\Components\Patients;
use Progforce\General\Components\Regression;
use Progforce\General\Components\SLP;
use Progforce\General\Console\BuildPerfectAcousticModel;
use Progforce\General\Console\CheckDictionaries;
use Progforce\General\Console\CheckPhonemes;
use Progforce\General\Console\CheckDBPhonemes;
use Progforce\General\Console\FixDictionaries;
use Progforce\General\Console\FixSounds;
use Progforce\General\Console\GenerateAcousticModel;
use Progforce\General\Console\GenerateCSV;
use Progforce\General\Console\LoadImages;
use Progforce\General\Console\UpdateDevices;
use Progforce\General\Console\TransferPhases;
use Progforce\General\Console\UpdateMedia;
use Progforce\General\Console\UpdateWords;
use Progforce\General\ReportWidgets\Android;
use Progforce\General\ReportWidgets\AssetsParser;
use Progforce\General\ReportWidgets\DictionariesStatus;
use Progforce\General\ReportWidgets\Logs;
use Progforce\General\ReportWidgets\VersionController;
use System\Classes\PluginBase;
use Progforce\General\Console\RemoveHeMP3;

class Plugin extends PluginBase
{
    public function registerReportWidgets() {
        return [
            Android::class => [
                'label'   => 'Android',
                'context' => 'dashboard'
            ],
            VersionController::class => [
                'label'   => 'Version Controller',
                'context' => 'dashboard'
            ],
            AssetsParser::class => [
                'label'   => 'Assets Parser',
                'context' => 'dashboard'
            ],
            Logs::class => [
                'label' => 'Logs',
                'context' => 'dashboard'
            ],
            DictionariesStatus::class => [
                'label' => 'Dictionaries Status',
                'context' => 'dashboard'
            ]
        ];
    }

    public function registerComponents()
    {
        return [
            Patients::class => 'patients',
            Patient::class => 'patient',
            SLP::class => 'slp',
            Regression::class => 'regression'
        ];
    }

    public function registerSettings() {
        return [
            'settings' => [
                'label'       => 'General Config',
                'description' => 'Manage General config.',
                'category'    => 'General Config',
                'icon'        => 'icon-desktop',
                'permissions' => ['progforce.general.access_settings'],
                'class'       => 'Progforce\General\Models\GenConfig',
                'order'       => 500,
                'keywords'    => '',
            ]
        ];
    }

    public function boot()
    {
        Validator::extend('uniqsound', function($attribute, $value, $parameters, $validator) {
  //          $data = $validator->getData();
   //         $id = array_get($data, 'id', null);
  //          $qry =  Models\Sound::where('language_id', $data['language_id'])->
   //             where('sound', $data['sound']);
  //          if ($id) {
   //             $qry = $qry->where('id', '<>', $id);
   //         }
   //         $sound = $qry->first();
   //         return !$sound;
            return true;
        });

        \App::register(GoogleDriveServiceProvider::class);
    }

    public function register()
    {
        $this->registerConsoleCommand('progforce.remove-mp3', RemoveHeMP3::class);
        $this->registerConsoleCommand('progforce.update-devices', UpdateDevices::class);
        $this->registerConsoleCommand('progforce.load-images', LoadImages::class);
        $this->registerConsoleCommand('progforce.generate-am', GenerateAcousticModel::class);
        $this->registerConsoleCommand('progforce.generate-csv', GenerateCSV::class);
        $this->registerConsoleCommand('progforce:build-perfect-am', BuildPerfectAcousticModel::class);
        $this->registerConsoleCommand('progforce:transfer-phases', TransferPhases::class);
        $this->registerConsoleCommand('progforce:update-media', UpdateMedia::class);
        $this->registerConsoleCommand('progforce:check-dictionaries', CheckDictionaries::class);
        $this->registerConsoleCommand('progforce:check-phonemes', CheckPhonemes::class);
        $this->registerConsoleCommand('progforce:check-db-phonemes', CheckDBPhonemes::class);
        $this->registerConsoleCommand('progforce:fix-dictionaries', FixDictionaries::class);
        $this->registerConsoleCommand('progforce:update-words', UpdateWords::class);
        $this->registerConsoleCommand('progforce:fix-sounds', FixSounds::class);
    }

    public function registerSchedule($schedule) {
        //$schedule->command('progforce:load-images')->daily();
        $schedule->command(
                'db:backup' .
                ' --database=mysql' .
                ' --destination=local' .
                ' --destinationPath=/db-' .
                ' --timestamp=d-m-Y' .
                ' --compression=gzip'
            )->daily()->at('14:55');
    }
}
