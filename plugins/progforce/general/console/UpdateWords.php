<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Models\Sound;

class UpdateWords extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'progforce:update-words';

    /**
     * @var string The console command description.
     */
    protected $description = 'Words table according request';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Started....');
    //    $this->updateWords('Progforce\General\Models\Word', 1);
        $this->updateWords('Progforce\General\Models\WordHeIl', 2);
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

    private function updateWords($model) {
        traceLog('Console Update Words started!');
        $sound = Sound::where('sound', 'ס')->first();
        if (!$sound) { $this->output->writeln('Sound not found!'); return; }

        $res = '';
        $words = $model::from('progforce_general_words_he_il as w')->
            select('w.id', 'w.word')->
            leftJoin('progforce_general_treatment_sounds as s', 's.id', '=', 'w.sound_id')->
            where('s.sound', 'ש')->where('w.phoneme', 's2')->
            get();
        $ids = array_column($words->toArray(), 'id');
        foreach ($words as $word) {
            $res .=  sprintf(' id - %s %s', $word->id, $word->word);
            $this->output->writeln('Word ' . $word->id . ' - ' . $word->word);
        }
        $model::whereIn('id', $ids)
          ->update(['sound_id' => $sound->id]);

        traceLog('Console Update Results:');
        traceLog($res);
    }

}
