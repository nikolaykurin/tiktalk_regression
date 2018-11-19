<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Progforce\General\Classes\Helpers\PathHelper;

class UpdateMedia extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'progforce:update-media';

    /**
     * @var string The console command description.
     */
    protected $description = 'Updates media fields for Words tables';
    private $report = [];

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Started....');
        $this->updateWords('Progforce\General\Models\Word', 'en-us', '');
        $this->updateWords('Progforce\General\Models\WordHeIl', 'he-il', '_he');
        $this->output->writeln('Done!');
        
        foreach($this->report as $langCode => $info) {
            $this->output->writeln($langCode);
            $this->output->writeln('Words count: ' . $info['wordsCount']);
            $this->output->writeln('Unique Words found: ' . count($info['ids']));
            $this->output->writeln('Images Found: ' . $info['imgFound']);
            $this->output->writeln('Audio Found: ' . $info['audioFound']);
            $this->output->writeln('---');
        }
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

    private function updateWords($model, $langCode, $sfx) {
        $words = $model::get();
        $this->report[$langCode] = ['ids' => [], 'imgFound' => 0, 'audioFound' => 0,  'wordsCount' => count($words)];
        $imagesPath = PathHelper::getImagesPath($langCode);
        $audioPath = PathHelper::getWordsAudioPath($langCode);
        foreach ($words as $word) {
            $audioFileName = $word->word_id . '_audio' . $sfx;
            $audioExists = Storage::disk('local')->exists($audioPath . '/' . $audioFileName . '.mp3') || 
                Storage::disk('local')->exists($audioPath .'/'. $audioFileName . '.wav');

            $imgExists = $word->hasAllImages();

            if (!in_array($word->word_id, $this->report[$langCode]['ids'])) {
                $this->report[$langCode]['ids'][] = $word->word_id;
                if ($imgExists) {
                    $this->report[$langCode]['imgFound'] += 1;
                }
                if ($audioExists) {
                    $this->report[$langCode]['audioFound'] += 1;
                }
            }

            $word->has_audio = $audioExists;
            $word->has_image = $imgExists;
            $word->save();
            $this->output->writeln('Word ' . $word->word_id . ' - ' . $word->word);
        }
    }
}
