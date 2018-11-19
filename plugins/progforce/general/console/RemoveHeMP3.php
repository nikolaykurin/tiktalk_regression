<?php namespace Progforce\General\Console;

use Storage;
use Illuminate\Console\Command;
use Progforce\General\Models\WordHeIl;
use Progforce\General\Classes\Helpers\PathHelper;

class RemoveHeMP3 extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'progforce:remove-mp3';

    /**
     * @var string The console command description.
     */
    protected $description = 'Removes MP3';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Started....');
        $this->removeAudio();
        $this->output->writeln('Done!');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

    private function removeAudio() {
        $words = WordHeIl::get();
        $audioPath = PathHelper::getWordsAudioPath('he-il');
        foreach ($words as $word) {
            $audioFileName = $word->word_id . '_audio_he';

            $audioExists = Storage::disk('local')->exists($audioPath . '/' . $audioFileName . '.mp3') && 
                Storage::disk('local')->exists($audioPath .'/'. $audioFileName . '.wav');
            if ($audioExists) {
                Storage::disk('local')->delete($audioPath . '/' . $audioFileName . '.mp3');
            }
            $this->output->writeln('Word ' . $word->word_id . ' - ' . $word->word);
        }
    }

}
