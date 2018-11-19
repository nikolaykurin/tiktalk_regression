<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Classes\AcousticModelAdapter;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Classes\Helpers\TranscriptionHelper;
use Progforce\General\Models\LanguageConfiguration;

class FixDictionaries extends Command {

    protected $name = 'progforce:fix-dictionaries';

    protected $description = 'Check DB and dictionaries for discrepancies';

    public function handle() {
        $langs = LanguageConfiguration::all();

        foreach ($langs as $lang) {
            AcousticModelHelper::fixDictionary($lang);
        }

        $this->output->writeln('Done!');
    }

}
