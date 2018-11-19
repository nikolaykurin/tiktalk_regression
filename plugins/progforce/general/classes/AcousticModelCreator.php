<?php namespace Progforce\General\Classes;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Progforce\General\Classes\Helpers\PathHelper;
use Exception;

// TODO: constants to config/env file
class AcousticModelCreator {

    public static $DEFAULT_FILLER = '<s> SIL' . PHP_EOL . '</s> SIL' . PHP_EOL . '<sil> SIL';

    public static $train_text = 'text_train';

    public $debug;
    public $language;
    public $tmpPath;
    public $newModelPath;
    public $fileidsPath;
    public $transcriptionsPath;
    public $dictPath;
    public $trainTextPath;
    public $lmPath;
    public $lmBinPath;

    public function __construct($tmpPath, $language, $debug = false) {
        if (empty($tmpPath)) {
            throw new Exception('Path to user\'s model must not be empty!' );
        }

        if (empty($language)) {
            throw new Exception('Language param is required!');
        }

        $this->debug = $debug;

        $this->language = $language;
        $this->tmpPath = $tmpPath;
        $this->newModelPath = sprintf('%s/model/%s', Config::get('paths.pocketsphinx'), $this->language);

        if (!is_dir($this->newModelPath)) {
            mkdir($this->newModelPath);
        }

        $newModelNested = sprintf('%s/%s', $this->newModelPath, $this->language);

        if (!is_dir($newModelNested)) {
            mkdir($newModelNested);
        }

        $this->fileidsPath = sprintf('%s/etc/%s', $tmpPath, sprintf('%s_train.fileids', $language));
        $this->transcriptionsPath = sprintf('%s/etc/%s', $tmpPath, sprintf('%s_train.transcription', $language));
        $this->dictPath = sprintf('%s/etc/%s', $tmpPath, sprintf('%s.dic', $language));
        $this->trainTextPath = sprintf('%s/etc/%s.txt', $tmpPath, AcousticModelCreator::$train_text);
        $this->lmPath = sprintf('%s/etc/%s', $tmpPath, sprintf('temp-%s.lm', $language));
        $this->lmBinPath = sprintf('%s/etc/%s', $tmpPath, sprintf('%s.lm.bin', $language));
    }

    public function create() {
        try {
            $this->checkDependencies();
            $this->checkFiles();

            $this->createLanguageModel();

            $this->setupScripts();

            $this->setupConfig();

            $this->train();

            $this->move();
        } catch (Exception $e) {
            if (!$this->debug) {
                $this->clearAll();
            }

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function checkDependencies() {
        if (
            !is_file(Config::get('paths.sphinx_lm_convert'))
            ||
            !file_exists(Config::get('paths.sphinxtrain.file'))
        ) {
            throw new Exception('Sphinx is not installed!');
        }

        if (
            !file_exists(Config::get('paths.cmu_language_toolkit.text2wfreq'))
            ||
            !file_exists(Config::get('paths.cmu_language_toolkit.text2idngram'))
            ||
            !file_exists(Config::get('paths.cmu_language_toolkit.idngram2lm'))
        ) {
            throw new Exception('CMU Language Toolkit is not installed!');
        }
    }

    public function checkFiles() {
        if (
            !is_dir(sprintf('%s/etc', $this->tmpPath))
            ||
            !is_dir(sprintf('%s/wav', $this->tmpPath))
            ||
            !file_exists($this->trainTextPath)
        ) {
            throw new Exception('No data for new model generating!');
        }
    }

    public function createLanguageModel() {
        $tmpPath = '/usr/tmp';

        if (!is_dir($tmpPath)) {
            mkdir($tmpPath);
        }

        $textPath = sprintf('%s/etc/%s', $this->tmpPath, self::$train_text);

        shell_exec(sprintf('text2wfreq < %s | wfreq2vocab > %s.vocab', $this->trainTextPath, $textPath));
        shell_exec(sprintf('text2idngram -vocab %s.vocab < %s.txt > %s.idngram', $textPath, $textPath, $textPath));
        shell_exec(sprintf('idngram2lm -vocab_type 0 -idngram %s.idngram -vocab %s.vocab -arpa %s', $textPath, $textPath, $this->lmPath));
        shell_exec(sprintf('sphinx_lm_convert -i %s -o %s', $this->lmPath, $this->lmBinPath));
    }

    public function setupScripts() {
        $setupScriptLog = shell_exec(sprintf('cd %s && sphinxtrain -t %s setup', $this->tmpPath, $this->language));

        Log::debug($setupScriptLog);
    }

    public function setupConfig() {
        $CFG_WAVFILE_EXTENSION = Config::get('training.CFG_WAVFILE_EXTENSION');
        $CFG_WAVFILE_TYPE = Config::get('training.CFG_WAVFILE_TYPE');
        $CFG_N_TIED_STATES = Config::get('training.CFG_N_TIED_STATES');
        $DEC_CFG_NPART = Config::get('training.DEC_CFG_NPART');
        $CFG_CD_TRAIN = Config::get('training.CFG_CD_TRAIN');
        $CFG_FORCEDALIGN = Config::get('training.CFG_FORCEDALIGN');
        $CFG_VTLN = Config::get('training.CFG_VTLN');

        $configFile = sprintf('%s/etc/sphinx_train.cfg', $this->tmpPath);

        if (!$configFile) {
            throw new Exception('Config file wasn\'t create, looks like something went wrong!');
        }

        $content = file_get_contents($configFile);
        $search = [
            '$CFG_WAVFILE_EXTENSION = \'wav\';',
            '$CFG_WAVFILE_TYPE = \'mswav\';',
            '$CFG_N_TIED_STATES = 1000;',
            '$CFG_QUEUE_TYPE = "Queue";',
            '$DEC_CFG_NPART = 1;',
            '$CFG_CD_TRAIN = \'yes\';',
            '$CFG_LANGUAGEMODEL  = "$CFG_LIST_DIR/$CFG_DB_NAME.lm.DMP";',
            '$DEC_CFG_LANGUAGEMODEL  = "$CFG_BASE_DIR/etc/${CFG_DB_NAME}.lm.DMP";',
            '$DEC_CFG_MODEL_NAME = "$CFG_EXPTNAME.cd_${CFG_DIRLABEL}_${CFG_N_TIED_STATES}";',
            '$CFG_FORCEDALIGN = \'no\';',
            '$CFG_VTLN = \'no\';',
        ];
        $replace = [
            sprintf('$CFG_WAVFILE_EXTENSION = \'%s\';', $CFG_WAVFILE_EXTENSION),
            sprintf('$CFG_WAVFILE_TYPE = \'%s\';', $CFG_WAVFILE_TYPE),
            sprintf('$CFG_N_TIED_STATES = %s;', $CFG_N_TIED_STATES),
            '$CFG_QUEUE_TYPE = "Queue::POSIX";',
            sprintf('$DEC_CFG_NPART = %s;', $DEC_CFG_NPART),
            sprintf('$CFG_CD_TRAIN = \'%s\';', $CFG_CD_TRAIN),
            '$CFG_LANGUAGEMODEL  = "$CFG_LIST_DIR/$CFG_DB_NAME.lm.bin";',
            '$DEC_CFG_LANGUAGEMODEL  = "$CFG_BASE_DIR/etc/${CFG_DB_NAME}.lm.bin";',
            '$DEC_CFG_MODEL_NAME = "$CFG_EXPTNAME.ci_cont";',
            sprintf('$CFG_FORCEDALIGN = \'%s\';', $CFG_FORCEDALIGN),
            sprintf('$CFG_VTLN = \'%s\';', $CFG_VTLN),
        ];

        file_put_contents($configFile, str_replace($search, $replace, $content));
    }

    public function train() {
        $trainLog = shell_exec(sprintf('cd %s && sphinxtrain run', $this->tmpPath));

        Log::debug($trainLog);
    }

    public function move() {
        $createdModelPath = sprintf('%s/model_parameters/%s', $this->tmpPath, sprintf('%s.ci_cont', $this->language));

        if (!is_dir($createdModelPath)) {
            throw new Exception('Model wasn\'t built...');
        }

        shell_exec(sprintf('cp -a %s/* %s/%s', $createdModelPath, $this->newModelPath, $this->language));
        shell_exec(sprintf('cp %s %s/cmudict-%s.dict', $this->dictPath, $this->newModelPath, $this->language));
        shell_exec(sprintf('cp %s %s/%s.lm.bin', $this->lmBinPath, $this->newModelPath, $this->language));

        if (!$this->debug) {
            $this->clearAll();
        }
    }

    public function clearAll() {
        PathHelper::RMDIRRecursively($this->tmpPath);
    }

}