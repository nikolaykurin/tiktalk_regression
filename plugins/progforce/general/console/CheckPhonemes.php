<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Models\LanguageConfiguration;
use Illuminate\Support\Facades\Config;

class CheckPhonemes extends Command {

    protected $name = 'progforce:check-phonemes';

    protected $description = 'Check phonemes in dictionaries';

    public function handle() {
        $langs = LanguageConfiguration::all();

        $result = [];
        foreach ($langs as $lang) {
            $result[$lang->language] = AcousticModelHelper::checkDictionaryPhonemes($lang);
        }

        dd($result);
    }

}
