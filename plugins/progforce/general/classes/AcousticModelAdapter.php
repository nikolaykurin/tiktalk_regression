<?php namespace Progforce\General\Classes;

use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Symfony\Component\Process\Process;
use FilesystemIterator;
use Exception;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AcousticModelAdapter {

    public static $RATE = 16000;

    public static $transcriptions = 'transcriptions.transcription';
    public static $fileids = 'fileids.fileids';
    public static $mllr_adapt = 'mllr_adapt';
    public static $map_adapt = 'map_adapt';
    public static $recognized = 'recognized.txt';

    public $language;
    public $transcriptionsPath;
    public $hmmPath;
    public $dictPath;
    public $fileidsPath;
    public $recordPath;
    public $mllrPath;
    public $mapPath;

    /**
     * AcousticAdapter constructor.
     * @param $modelPath
     * @param $language
     * @throws Exception
     */
    public function __construct($modelPath, $language) {
        if (empty($modelPath)) {
            throw new Exception('Path to user\'s model must not be empty!' );
        }

        if (empty($language)) {
            throw new Exception('Language param is required!');
        }

        AcousticModelHelper::checkLanguageAllowed($language);

        $this->language = $language;

        $this->transcriptionsPath = sprintf('%s/%s', $modelPath, self::$transcriptions);
        $this->hmmPath = sprintf('%s/%s', $modelPath, $language);
        $this->dictPath = sprintf('%s/%s', $modelPath, sprintf('cmudict-%s.dict', $language));
        $this->lmPath = sprintf('%s/%s', $modelPath, sprintf('%s.lm.bin', $language));
        $this->fileidsPath = sprintf('%s/%s', $modelPath, self::$fileids);
        $this->recordPath = $modelPath;
        $this->mllrPath = sprintf('%s/%s_%s', $modelPath, self::$mllr_adapt, $this->language);
        $this->mapPath = sprintf('%s/%s_%s', $modelPath, self::$map_adapt, $this->language);

        if (!is_dir($this->mapPath)) {
            mkdir($this->mapPath);
        }
    }

    /**
     * Run all commands for generating MLLR adaptation file
     * @throws Exception
     */
    public function mllr_adaptation() {
        try {
            $this->checkDependencies();

            $this->checkFiles();
            $this->copyModel();

            $this->sphinx_fe();
            $this->mdef_convert();
            $this->bw();

            $this->mllr_solve();

            $this->clearUnnecessary();
        } catch (Exception $e) {
            $this->clearAll();

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Run all commands for generating MAP adaptation model
     * @throws Exception
     */
    public function map_adaptation() {
        try {
            $this->checkDependencies();

            $this->checkFiles();
            $this->copyModel();

            $this->sphinx_fe();
            $this->mdef_convert();
            $this->bw();

            $this->cloneModel();

            $this->map_adapt();
            $this->mk_s2sendump();

            $this->clearUnnecessary();
        } catch (Exception $e) {
            $this->clearUnnecessary();

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Check that Sphinxtrain installed
     * @throws Exception
     */
    public function checkDependencies() {
        if (!is_dir(Config::get('paths.sphinxtrain.folder'))) {
            throw new Exception('Sphinx is not installed!');
        }
    }

    /**
     * Check that user's model directory contains at least one *.wav, transcriptions.transcription and fileids.fileids files
     * @throws Exception
     */
    public function checkFiles() {
        $iterator = new FilesystemIterator($this->recordPath, FilesystemIterator::SKIP_DOTS);
        $count = iterator_count($iterator);

        if (
            $count < 3
        ||
            !file_exists($this->transcriptionsPath)
        ||
            !file_exists($this->fileidsPath)
        ) {
            throw new Exception('User\'s model directory doesn\'t contain files for adaptation!');
        }
    }

    /**
     * Copy acoustic model to user's model directory
     * @throws ProcessFailedException
     */
    public function copyModel() {
        $pathToModel = sprintf('%s/%s', Config::get('paths.pocketsphinx'), 'model');

        $process = new Process(
            sprintf('cp -a %s/%s/%s ', $pathToModel, $this->language, $this->language) . $this->recordPath .
            ' && ' .
            sprintf('cp -a %s/%s/%s ', $pathToModel, $this->language, sprintf('cmudict-%s.dict', $this->language)) . $this->recordPath .
            ' && ' .
            sprintf('cp -a %s/%s/%s ', $pathToModel, $this->language, sprintf('%s.lm.bin', $this->language)) . $this->recordPath
        );
        $process->setTimeout(0);
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Generate dictionary from audios
     * @throws ProcessFailedException
     */
    public function generateDictionary() {
        $pathToModel = sprintf('%s/%s', Config::get('paths.pocketsphinx'), 'model');

        $process_1 = new Process(
            'pocketsphinx_batch ' .
            ' -hmm ' . $this->hmmPath .
            ' -lm ' . $this->lmPath .
            ' -dict ' . $this->dictPath .
            ' -cepdir ' . $this->recordPath .
            ' -ctl ' . $this->fileidsPath .
            ' -cepext ' . '.wav' .
            ' -adcin ' . 'yes' .
            ' -hyp ' . sprintf('%s/%s', $this->recordPath, self::$recognized)
        );
        $process_1->setTimeout(0);
        $process_1->start();
        $process_1->wait();

        if (!$process_1->isSuccessful()) {
            throw new ProcessFailedException($process_1);
        }

        $process_2 = new Process(
            '/usr/local/bin/g2p-seq2seq --decode ' . sprintf('%s/%s', $this->recordPath, self::$recognized) . ' --model /home/kolyank/g2p-seq2seq-cmudict'
        );
        $process_2->setTimeout(0);
        $process_2->start();
        $process_2->wait();

        if (!$process_2->isSuccessful()) {
            throw new ProcessFailedException($process_2);
        }
    }

    /**
     * Generate acoustic feature files
     * @throws ProcessFailedException
     */
    public function sphinx_fe() {
        // samprate can be different
        $process = new Process(
            'sphinx_fe' .
            ' -argfile ' . sprintf('%s/%s', $this->hmmPath,'feat.params') .
            ' -samprate ' . strval(self::$RATE) .
            ' -c ' . $this->fileidsPath .
            ' -di ' . $this->recordPath .
            ' -do ' . $this->recordPath .
            ' -ei ' . 'wav' .
            ' -eo ' . 'mfc' .
            ' -mswav ' . 'yes'
        );
        $process->setTimeout(0);
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Convert mdef file to mdef.txt
     * @throws ProcessFailedException
     */
    public function mdef_convert() {
        $process = new Process(
            sprintf('pocketsphinx_mdef_convert -text %s %s',
                sprintf('%s/%s', $this->hmmPath, 'mdef'),
                sprintf('%s/%s.txt', $this->hmmPath, 'mdef')
            )
        );
        $process->setTimeout(0);
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Accumulate observation counts
     * @throws ProcessFailedException
     */
    public function bw() {
        $process = new Process(
            sprintf('%s/%s', Config::get('paths.sphinxtrain.folder'), 'bw') .
            ' -hmmdir ' . $this->hmmPath .
            ' -moddeffn ' . sprintf('%s/%s.txt', $this->hmmPath, 'mdef') .
            ' -ts2cbfn ' . '.cont.' .
            ' -feat ' . '1s_c_d_dd' .
            ' -cmn ' . 'current' .
            ' -agc ' . 'none' .
            ' -dictfn ' . $this->dictPath .
            ' -ctlfn ' . $this->fileidsPath .
            ' -lsnfn ' . $this->transcriptionsPath .
            ' -cepdir ' . $this->recordPath .
            ' -accumdir ' . $this->recordPath
        );
        $process->setTimeout(0);
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Generate mllr_matrix file
     * @throws ProcessFailedException
     */
    public function mllr_solve() {
        $process = new Process(
            sprintf('%s/%s', Config::get('paths.sphinxtrain.folder'), 'mllr_solve') .
            ' -meanfn ' . sprintf('%s/%s', $this->hmmPath, 'means') .
            ' -varfn ' . sprintf('%s/%s', $this->hmmPath, 'variances') .
            ' -outmllrfn ' . $this->mllrPath .
            ' -accumdir ' . $this->recordPath
        );
        $process->setTimeout(0);
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Delete in model directory all files except "mllr_adapt*"
     * @throws ProcessFailedException
     */
    public function clearUnnecessary() {
        $process = new Process(
            sprintf('find %s/* -maxdepth 0 -type f ! -name "%s*" ! -name "*.zip" -delete', $this->recordPath, self::$mllr_adapt) .
            ' && ' .
            sprintf('rm -rf %s/%s', $this->recordPath, $this->language)
        );
        $process->setTimeout(0);
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (is_dir($this->mapPath)) {
            shell_exec(sprintf('rm %s/mdef.txt', $this->mapPath));
        }
    }

    /**
     * Delete all in model directory
     */
    public function clearAll() {
        PathHelper::RMDIRRecursively($this->recordPath);
    }

    /**
     * Clone model to "map_adapt" folder
     * @throws ProcessFailedException
     */
    public function cloneModel() {
        shell_exec(sprintf('cp -a %s/%s/* %s', $this->recordPath, $this->language, $this->mapPath));
    }

    /**
     * Updating the acoustic model files with MAP
     * @throws ProcessFailedException
     */
    public function map_adapt() {
        $process = new Process(
            sprintf('%s/%s', Config::get('paths.sphinxtrain.folder'), 'map_adapt') .
            ' -meanfn ' . sprintf('%s/%s', $this->hmmPath, 'means') .
            ' -varfn ' . sprintf('%s/%s', $this->hmmPath, 'variances') .
            ' -mixwfn ' .  sprintf('%s/%s', $this->hmmPath, 'mixture_weights') .
            ' -tmatfn ' .  sprintf('%s/%s', $this->hmmPath, 'transition_matrices') .
            ' -accumdir ' . $this->recordPath .
            ' -mapmeanfn ' . sprintf('%s/%s', $this->mapPath, 'means') .
            ' -mapvarfn ' . sprintf('%s/%s', $this->mapPath, 'variances') .
            ' -mapmixwfn ' . sprintf('%s/%s', $this->mapPath, 'mixture_weights') .
            ' -maptmatfn ' . sprintf('%s/%s', $this->mapPath, 'transition_matrices')
        );
        $process->setTimeout(0);
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Recreating the adapted sendump file
     * @throws ProcessFailedException
     */
    public function mk_s2sendump() {
        $process = new Process(
            sprintf('%s/%s', Config::get('paths.sphinxtrain.folder'), 'mk_s2sendump') .
            ' -pocketsphinx ' . 'yes' .
            ' -moddeffn ' . sprintf('%s/%s.txt', $this->mapPath, 'mdef') .
            ' -mixwfn ' .  sprintf('%s/%s', $this->mapPath, 'mixture_weights') .
            ' -sendumpfn ' .  sprintf('%s/%s', $this->mapPath, 'sendump')
        );
        $process->setTimeout(0);
        $process->start();
        $process->wait();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

}