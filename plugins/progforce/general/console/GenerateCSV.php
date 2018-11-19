<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Progforce\General\Classes\AcousticModelCreator;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateCSV extends Command {

    protected $name = 'progforce:generate-csv';

    protected $description = 'Generate CSV from existed data array';

    public function handle() {
        $options = $this->option();

        $file = $options['file'];
        $lang = $options['lang'];

        if (is_null($lang) || is_null($file)) {
            $this->output->error('Options "lang" and "file" required');
            $this->output->error('Ends with error');

            return;
        }

        $this->output->writeln('Checking file...');

        $path = PathHelper::getModelTempPath($lang);
        $zipPath = $path . DIRECTORY_SEPARATOR . $file;

        if (!is_dir($path) || !file_exists($zipPath)) {
            $this->output->error('Directory or file not exists');
            $this->output->error('Ends with error');

            return;
        } else {
            $this->output->success('File found');
        }

        $this->output->writeln('Unpacking...');

        Zipper::make($zipPath)->extractTo($path);

        $this->output->success('Unpacked');

        try {
            $this->output->writeln('Start data preparing...');

            AcousticModelHelper::buildCSV($path, $lang);

            $this->output->success('Data prepared');

        } catch (Exception $e) {
            $this->output->error($e->getMessage());
            $this->output->error('Ends with error');

            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return;
        }

        $this->output->success('Done!');
    }

    protected function getOptions()
    {
        return [
            [ 'lang', null, InputOption::VALUE_REQUIRED, 'Language code', null ],
            [ 'file', null, InputOption::VALUE_REQUIRED, 'File name', null ],
        ];
    }

}