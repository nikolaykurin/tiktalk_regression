<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Models\Sound;
use Progforce\General\Models\LanguageConfiguration;

class FixSounds extends Command {

    protected $name = 'progforce:fix-sounds';

    protected $description = 'Remove all not used sounds';

    public function handle() {
        $this->output->writeln('Started....');

        $langs = LanguageConfiguration::all();

        $soundIds = Sound::select('id')->pluck('id')->toArray();

        $soundIdsFromWords = [];
        foreach ($langs as $lang) {
            $langSoundIdsFromWords = ('\\Progforce\\General\\Models\\' . $lang->words_table)::select('sound_id')->distinct()->pluck('sound_id')->toArray();

            $soundIdsFromWords = array_merge($soundIdsFromWords, $langSoundIdsFromWords);
        }

        $soundIdsForDelete = array_diff($soundIds, $soundIdsFromWords);

        $this->output->writeln('Sounds with this IDs will be deleted: ' . json_encode(array_values($soundIdsForDelete)));

        Sound::destroy($soundIdsForDelete);

        $this->output->writeln('Done!');
    }

}
