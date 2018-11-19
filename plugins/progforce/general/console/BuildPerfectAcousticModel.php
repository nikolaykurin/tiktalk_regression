<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\AcousticModelAdapter;
use Symfony\Component\Console\Input\InputOption;
use Progforce\General\Classes\AcousticModelCreator;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Log;
use Exception;

class BuildPerfectAcousticModel extends Command {

    protected $name = 'progforce:build-perfect-am';

    protected $description = 'Build Perfect AM from existed data array';

    public function handle() {
        $start = microtime(true);

        $options = $this->option();

        $file = $options['file'];
        $lang = $options['lang'];
        $debug = Config::get('training.DEBUG');

        if (is_null($lang) || is_null($file)) {
            $this->output->error('Options "lang" and "file" required');
            $this->output->error('Ends with error');

            return;
        }

        $this->output->writeln('Checking file...');

        $path = PathHelper::getPerfectSpeakerAbsoluteModelPath();
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

        if (!$debug) {
            unlink($zipPath);
        }

        $this->output->success('Unpacked');

        try {
            $this->output->writeln('Start data preparing...');

            if ($lang === 'he-il') {
                AcousticModelHelper::prepareFilesForAdapt_v2($path, $lang);
            } else {
                AcousticModelHelper::prepareFilesForAdapt($path, $lang);
            }

            $folders = PathHelper::getFolders($path);
            foreach ($folders as $folder) {
                if (strpos($folder, AcousticModelAdapter::$map_adapt) !== false) {
                    continue;
                }

                PathHelper::RMDIRRecursively(sprintf('%s/%s', $path, $folder));
            }

            $this->output->success('Data prepared');

            $this->output->writeln('Start perfect AM building...');

            $adapter = new AcousticModelAdapter($path, $lang);
            $adapter->map_adaptation();
        } catch (Exception $e) {
            $this->output->error($e->getMessage());
            $this->output->error('Ends with error');

            Log::debug($e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return;
        }

        $end = microtime(true);

        $total = $end - $start;

        $this->output->success('Done!');
        $this->output->writeln(sprintf('Execution time: %dm (%ds)', $total / 60, $total));
    }

    protected function getOptions()
    {
        return [
            [ 'lang', null, InputOption::VALUE_REQUIRED, 'Language code', null ],
            [ 'file', null, InputOption::VALUE_REQUIRED, 'File name', null ],
        ];
    }

}