<?php namespace Progforce\General\Classes\Helpers;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Progforce\General\Classes\AcousticModelAdapter;
use Progforce\General\Classes\AcousticModelCreator;
use Progforce\General\Models\LanguageConfiguration;
use Progforce\User\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Chumper\Zipper\Zipper;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RecursiveCallbackFilterIterator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Exception;

class AcousticModelHelper {

    public static $DATA_FILE = 'data.csv';
    public static $CONFIG_FILE = 'sphinx_train.cfg';
    public static $DATA_FILE_TYPE = 'Csv';

    public static $EMPTY_STRING_DELIMITER = '';
    public static $UNDERSCORE_DELIMITER = '_';
    public static $SPACE_DELIMITER = ' ';
    public static $LANGUAGE_CODE_DELIMITER = '-';
    public static $EXTENSION_DELIMITER = '.';
    public static $COMMA_DELIMITER = ',';
    public static $BACKSLASH_DELIMITER = '\\';
    public static $TAB_DELIMITER = "\t";
    public static $WORD_PREFIX = 'sc';
    public static $WORD_DIRECTORY_CODE = 'DIR';
    public static $WORD_SOURCES_CODE = 'SRC';
    public static $WORD_VALUE_CODE = 'LBR';
    public static $WORD_TRANSCRIPTION_CODE = 'LBO';

    public static $PHONES_TRASH = [
        '',
        ' ',
        "\n"
    ];
    public static $TRANSCRIPTIONS_TRASH = [
        '[G A r]',
        '[F I L ]',
        '*',
        '"'
    ];

    public static function generateUserModelZip($user_code) {
        $user = User::findOrFail($user_code);

        $modelPath = sprintf('%s/%s_%s', PathHelper::getUserAbsoluteModelPath($user->id), AcousticModelAdapter::$map_adapt, $user->language->language);
        $zipPath = sprintf('%s.zip', $modelPath);

        if (!is_dir($modelPath) || PathHelper::checkDirectoryEmpty($modelPath)) {
            throw new Exception('User\'s model not created yet');
        }

        self::pack($modelPath, $zipPath);

        return $zipPath;
    }

    public static function generatePerfectModelZip($lang) {
        $modelPath = sprintf('%s/%s_%s', PathHelper::getPerfectSpeakerAbsoluteModelPath(), AcousticModelAdapter::$map_adapt, $lang);
        $lm = sprintf('%s/%s/%s/%s', Config::get('paths.pocketsphinx'), 'model', $lang, sprintf('%s.lm.bin', $lang));
        $dic = sprintf('%s/%s/%s/%s', Config::get('paths.pocketsphinx'), 'model', $lang, sprintf('cmudict-%s.dict', $lang));
        shell_exec(sprintf('cp %s %s/dictionary.lm.bin', $lm, $modelPath));
        shell_exec(sprintf('cp %s %s/dictionary.dict', $dic, $modelPath));

        $zipPath = sprintf('%s.zip', $modelPath);

        if (!is_dir($modelPath)) {
            throw new Exception('Perfect AM is not created yet! Path: ' . $modelPath);
        }

        if (!file_exists($zipPath)) {
            self::pack($modelPath, $zipPath);
        }

        return $zipPath;
    }

    public static function pack($folder, $zipName) {
        $imagesList = glob($folder);

        $zipper = new Zipper();
        $zipper->make($zipName)->add($imagesList);
        $zipper->close();
    }

    public static function checkLanguageAllowed($language) {
        $supportedLanguages = LanguageConfiguration::all()->pluck('language')->toArray();

        if (!in_array($language, $supportedLanguages)) {
            throw new Exception(sprintf('Dictionary not configured for %s language!', $language));
        }
    }

    public static function prepareFilesForAdapt($path, $language) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        $fileNames = [];
        foreach ($files as $file) {

            $filePath = $file->getPathname();

            if (strpos($filePath, AcousticModelAdapter::$map_adapt) !== false) {
                continue;
            }

            if (!$file->isDir()) {
                $fileName = preg_replace('/_\d/', self::$EMPTY_STRING_DELIMITER, $file->getFilename());

                if (file_exists($path . DIRECTORY_SEPARATOR . $fileName) && $path !== $file->getPath()) {
                    $fileName = uniqid() . self::$UNDERSCORE_DELIMITER . $fileName;
                }

                rename($filePath, $path . DIRECTORY_SEPARATOR . $fileName);

                $fileNames[] = strstr(basename($fileName), '.', true);
            }

        }

        $words = WordsHelper::getWordsListByLang($fileNames, $language);

        if ($words->isEmpty()) {
            return response('Words not found in database!', 400);
        }

        $contentFileids = '';
        $contentTranscriptions = '';
        foreach ($fileNames as $filename) {
            $realName = $filename;
            $pos = strpos($filename, self::$UNDERSCORE_DELIMITER);

            if ($pos) {
                $filename = substr($filename, $pos + 1);
            }

            $word = $words->where('word_id', $filename)->first();

            if (!$word) {
                continue;
            }

            $contentFileids .= $realName . PHP_EOL;
            $contentTranscriptions .= sprintf('<s> %s </s> (%s)%s', $word->word, $realName, PHP_EOL);
        }

        file_put_contents(sprintf('%s/%s', $path, AcousticModelAdapter::$fileids), $contentFileids);
        file_put_contents(sprintf('%s/%s', $path, AcousticModelAdapter::$transcriptions), $contentTranscriptions);
    }

    public static function prepareFilesForAdapt_v2($path, $language) {
        $filePath = $path . DIRECTORY_SEPARATOR . self::$DATA_FILE;

        if (!file_exists($filePath)) {
            throw new Exception('Main data file not found!');
        }

        $headers = [];

        $contentFileids = '';
        $contentTranscriptions = '';

        $reader = IOFactory::createReader(self::$DATA_FILE_TYPE);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        for ($row = 1; $row <= $highestRow; ++$row) {

            if ($row === 1) {
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $headers[$worksheet->getCellByColumnAndRow($col, $row)->getValue()] = $col;
                }
            } else {
                $word = $worksheet->getCellByColumnAndRow($headers[self::$WORD_VALUE_CODE], $row)->getValue();
                $directory = $worksheet->getCellByColumnAndRow($headers[self::$WORD_DIRECTORY_CODE], $row)->getValue();
                $sources = $worksheet->getCellByColumnAndRow($headers[self::$WORD_SOURCES_CODE], $row)->getValue();
                $transcription = strtoupper(str_replace(self::$TRANSCRIPTIONS_TRASH, '', trim($worksheet->getCellByColumnAndRow($headers[self::$WORD_TRANSCRIPTION_CODE], $row)->getValue())));

                $fileItems = explode(self::$COMMA_DELIMITER, $sources);
                $pathItems = explode(self::$BACKSLASH_DELIMITER, $directory);

                $relativePath = '';
                foreach ($pathItems as $folder) {
                    $relativePath .= ucfirst(strtolower($folder)) . DIRECTORY_SEPARATOR;
                }

                if (empty($word) || empty($transcription)) {
                    $errors[] = [ $word, $transcription ];

                    continue;
                }

                $wordItems = explode(self::$SPACE_DELIMITER, $word);
                $transcriptionItems = explode(self::$TAB_DELIMITER, $transcription);

                if (count($wordItems) !== count($transcriptionItems)) {
                    $errors[] = [ $wordItems, $transcriptionItems ];

                    continue;
                }

                $file = str_replace('HE0', 'wav', $fileItems[0]);

                $fileNameWithoutExtension = explode(self::$EXTENSION_DELIMITER , $file)[0];

                $oldFilePath = $path . DIRECTORY_SEPARATOR . $relativePath . $file;
                $newFilePath = $path . DIRECTORY_SEPARATOR . $file;

                if (!file_exists($oldFilePath)) {
                    $oldFilePath = str_replace(strtoupper(self::$WORD_PREFIX), ucfirst(self::$WORD_PREFIX), $oldFilePath);
                }

                if (!file_exists($oldFilePath) || file_exists($newFilePath)) {
                    continue;
                }

                rename($oldFilePath, $newFilePath);

                $contentFileids_line = $fileNameWithoutExtension . PHP_EOL;
                $contentTranscriptions_line = sprintf('<s> %s </s> (%s)%s', $word, $fileNameWithoutExtension, PHP_EOL);

                $contentFileids .= $contentFileids_line;
                $contentTranscriptions .= $contentTranscriptions_line;

            }
        }

        file_put_contents(sprintf('%s/%s', $path, AcousticModelAdapter::$fileids), $contentFileids);
        file_put_contents(sprintf('%s/%s', $path, AcousticModelAdapter::$transcriptions), $contentTranscriptions);
    }

    public static function prepareFilesForCreate_v1($path, $language) {
        $wavesPath = sprintf('%s/wav', $path);
        $etcPath = sprintf('%s/etc', $path);
        $wavesExtension = 'wav';

        if (!is_dir($wavesPath)) {
            mkdir($wavesPath);
        }
        if (!is_dir($etcPath)) {
            mkdir($etcPath);
        }

        $filter = [
            'wav',
            'etc'
        ];

        $files = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator(
                    $path,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                function ($fileInfo, $key, $iterator) use ($filter) {
                    return $fileInfo->isFile() || !in_array($fileInfo->getBaseName(), $filter);
                }
            )
        );

        $fileNames = [];
        foreach ($files as $file) {

            $filePath = $file->getPathname();

            if (strpos($filePath, AcousticModelAdapter::$map_adapt)) {
                continue;
            }

            if (!$file->isDir()) {
                if ($file->getExtension() !== $wavesExtension) {
                    unlink($filePath);

                    continue;
                }

                $fileName = $file->getFilename();

                if (file_exists($wavesPath . DIRECTORY_SEPARATOR . $fileName)) {
                    $fileName = uniqid() . self::$UNDERSCORE_DELIMITER . $fileName;
                }

                rename($filePath, sprintf('%s/%s', $wavesPath, $fileName));

                $fileNames[] = strstr(basename($fileName), '.', true);
            }

        }

        $words = WordsHelper::getWordsListByLang($fileNames, $language);

        if ($words->isEmpty()) {
            return response('Words not found in database!', 400);
        }

        $contentFileids = '';
        $contentTranscriptions = '';
        $contentTextTrain = '';
        $dicArray = [];
        $phoneArray = [];

        // TEMPORARY FIX
        $usedWords = [];
        foreach ($fileNames as $filename) {
            $realName = $filename;
            $pos = strpos($filename, self::$UNDERSCORE_DELIMITER);

            if ($pos) {
                $filename = substr($filename, $pos + 1);
            }

            $word = $words->where('word_id', $filename)->first();

            if (!$word || !$word->transcription1) {
                continue;
            }

            // TEMPORARY FIX
            if (strstr($word->word, self::$SPACE_DELIMITER) !== false || in_array($word->word, $usedWords)) {
                continue;
            }

            $contentFileids .= $realName . PHP_EOL;
            $contentTranscriptions .= sprintf('<s> %s </s> (%s)%s', $word->word, $realName, PHP_EOL);
            $contentTextTrain .= sprintf('<s> %s </s>%s', $word->word, PHP_EOL);
            $dicArray[] = $word->word . self::$SPACE_DELIMITER . $word->transcription1 . PHP_EOL;
            $phoneArray = array_merge($phoneArray, explode(self::$SPACE_DELIMITER, $word->transcription1));

            $usedWords[] = $word->word;
        }

        $contentDic = implode(array_unique($dicArray));

        $phoneArray[] = 'SIL';
        $phoneArray = array_unique($phoneArray);
        sort($phoneArray);
        $contentPhone = implode(PHP_EOL, $phoneArray);

        if ( empty($contentDic) || empty($contentPhone) ) {
            throw new Exception('Dictionaries can\'t be collect.');
        }

        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s_train.fileids', $language)), $contentFileids);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s_train.transcription', $language)), $contentTranscriptions);
        file_put_contents(sprintf('%s/%s.txt', $etcPath, AcousticModelCreator::$train_text), $contentTextTrain);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s.filler', $language)), AcousticModelCreator::$DEFAULT_FILLER);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s.dic', $language)), $contentDic);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s.phone', $language)), $contentPhone);

        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s_test.fileids', $language)), $contentFileids);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s_test.transcription', $language)), $contentTranscriptions);
    }

    public static function prepareFilesForCreate_v2($path, $language, $debug = false) {
        $errors = [];

        $wavesExtension = Config::get('training.CFG_WAVFILE_EXTENSION');
        $testPercent = Config::get('training.TEST_PERCENT');

        $wavesPath = sprintf('%s/wav', $path);
        $etcPath = sprintf('%s/etc', $path);

        if (!is_dir($wavesPath)) {
            mkdir($wavesPath);
        }
        if (!is_dir($etcPath)) {
            mkdir($etcPath);
        }

        $filePath = $path . DIRECTORY_SEPARATOR . self::$DATA_FILE;
        $configPath = $path . DIRECTORY_SEPARATOR . self::$CONFIG_FILE;

        if (!file_exists($filePath)) {
            throw new Exception('Main data file not found!');
        }

        $index = 0;
        $headers = [];

        $contentFileids_train = '';
        $contentTranscriptions_train = '';
        $contentFileids_test = '';
        $contentTranscriptions_test = '';
        $contentTextTrain = '';

        $contentDicArray = [];
        $contentPhoneArray = [];

        $reader = IOFactory::createReader(self::$DATA_FILE_TYPE);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        for ($row = 1; $row <= $highestRow; ++$row) {

            if ($row === 1) {
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $headers[$worksheet->getCellByColumnAndRow($col, $row)->getValue()] = $col;
                }
            } else {
                $word = $worksheet->getCellByColumnAndRow($headers[self::$WORD_VALUE_CODE], $row)->getValue();
                $directory = $worksheet->getCellByColumnAndRow($headers[self::$WORD_DIRECTORY_CODE], $row)->getValue();
                $sources = $worksheet->getCellByColumnAndRow($headers[self::$WORD_SOURCES_CODE], $row)->getValue();
                $transcription = strtoupper(str_replace(self::$TRANSCRIPTIONS_TRASH, '', trim($worksheet->getCellByColumnAndRow($headers[self::$WORD_TRANSCRIPTION_CODE], $row)->getValue())));

                $fileItems = explode(self::$COMMA_DELIMITER, $sources);
                $pathItems = explode(self::$BACKSLASH_DELIMITER, $directory);

                $relativePath = '';
                foreach ($pathItems as $folder) {
                    $relativePath .= ucfirst(strtolower($folder)) . DIRECTORY_SEPARATOR;
                }

                if (empty($word) || empty($transcription)) {
                    $errors[] = [ $word, $transcription ];

                    continue;
                }

                $wordItems = explode(self::$SPACE_DELIMITER, $word);
                $transcriptionItems = explode(self::$TAB_DELIMITER, $transcription);

                if (count($wordItems) !== count($transcriptionItems)) {
                    $errors[] = [ $wordItems, $transcriptionItems ];

                    continue;
                }

                foreach ($fileItems as $file) {
                    $fileNameWithoutExtension = FileHelper::getFileNameWithoutExtension($file);

                    $oldFilePath = $path . DIRECTORY_SEPARATOR . $relativePath . $file;
                    $newFilePath = $wavesPath . DIRECTORY_SEPARATOR . $file;

                    if (!file_exists($oldFilePath)) {
                        $oldFilePath = str_replace(strtoupper(self::$WORD_PREFIX), ucfirst(self::$WORD_PREFIX), $oldFilePath);
                    }

                    if (!file_exists($oldFilePath)) {
                        continue;
                    }

//                    rename($oldFilePath, $newFilePath);
                    copy($oldFilePath, $newFilePath);

                    $contentFileids_line = $fileNameWithoutExtension . PHP_EOL;
                    $contentTranscriptions_line = sprintf('<s> %s </s> (%s)%s', $word, $fileNameWithoutExtension, PHP_EOL);

                    $contentFileids_train .= $contentFileids_line;
                    $contentTranscriptions_train .= $contentTranscriptions_line;
                    $contentFileids_test .= $index * $testPercent % 100 === 0 ? $contentFileids_line : self::$EMPTY_STRING_DELIMITER;
                    $contentTranscriptions_test .= $index * $testPercent % 100 === 0 ? $contentTranscriptions_line : self::$EMPTY_STRING_DELIMITER;

                    $index++;
                }

                for ($i = 0; $i < count($wordItems); $i++) {
                    $_word = trim($wordItems[$i]);
                    $_transcription = trim($transcriptionItems[$i]);

                    if (!array_key_exists($_word, $contentDicArray)) {
                        $contentDicArray[$_word] = $_word . self::$SPACE_DELIMITER . $_transcription . PHP_EOL;
                        $contentPhoneArray = array_merge($contentPhoneArray, explode(self::$SPACE_DELIMITER, $_transcription));
                    }
                }

                $contentTextTrain .= sprintf('<s> %s </s>%s', $word, PHP_EOL);

            }
        }

        if (empty($contentDicArray) || empty($contentPhoneArray)) {
            throw new Exception('Dictionaries can\'t be collect.');
        }

        $contentPhoneArray[] = 'SIL';
        $contentPhoneArray = array_unique(array_diff($contentPhoneArray, self::$PHONES_TRASH));
        sort($contentPhoneArray);

        $contentDic = implode(array_values($contentDicArray));
        $contentPhone = implode(PHP_EOL, $contentPhoneArray);

        if (!empty($errors)) {
            Log::debug('Can\'t use this data in dictionary', $errors);
        }

        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s_train.fileids', $language)), $contentFileids_train);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s_train.transcription', $language)), $contentTranscriptions_train);
        file_put_contents(sprintf('%s/%s.txt', $etcPath, AcousticModelCreator::$train_text), $contentTextTrain);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s.filler', $language)), AcousticModelCreator::$DEFAULT_FILLER);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s.dic', $language)), $contentDic);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s.phone', $language)), $contentPhone);

        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s_test.fileids', $language)), $contentFileids_test);
        file_put_contents(sprintf('%s/%s', $etcPath, sprintf('%s_test.transcription', $language)), $contentTranscriptions_test);

        if (file_exists($configPath)) {
            rename($configPath, sprintf('%s/%s', $etcPath, 'config'));
        }
    }

    public static function buildCSV($path, $language) {
        // TODO: WTF with this formats?!
        $languageCode = strtolower(explode(self::$LANGUAGE_CODE_DELIMITER, $language)[0]);
        $wordDataFileExtension = ucfirst($languageCode) . 'o';

        $filePath = $path . DIRECTORY_SEPARATOR . self::$DATA_FILE;

        if (!file_exists($filePath)) {
            throw new Exception('Main data file not found!');
        }

        $headers = [];

        $reader = IOFactory::createReader(self::$DATA_FILE_TYPE);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        $data = [];
        for ($row = 1; $row <= $highestRow; ++$row) {

            if ($row === 1) {
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $headers[$worksheet->getCellByColumnAndRow($col, $row)->getValue()] = $col;
                }
            } else {
                $word = null;
                $directory = $worksheet->getCellByColumnAndRow($headers[self::$WORD_DIRECTORY_CODE], $row)->getValue();
                $sources = $worksheet->getCellByColumnAndRow($headers[self::$WORD_SOURCES_CODE], $row)->getValue();
                $transcription = strtoupper(str_replace(self::$TRANSCRIPTIONS_TRASH, '', trim($worksheet->getCellByColumnAndRow($headers[self::$WORD_TRANSCRIPTION_CODE], $row)->getValue())));

                $fileItems = explode(self::$COMMA_DELIMITER, $sources);
                $pathItems = explode(self::$BACKSLASH_DELIMITER, trim($directory, self::$BACKSLASH_DELIMITER));

                $relativePath = '';
                foreach (array_slice($pathItems, 1) as $folder) {
                    $relativePath .= ucfirst(strtolower($folder)) . DIRECTORY_SEPARATOR;
                }

                $fileName = str_replace(strtoupper(self::$WORD_PREFIX), ucfirst(self::$WORD_PREFIX), explode(self::$EXTENSION_DELIMITER , $fileItems[0])[0]) . '.' . $wordDataFileExtension;
                $heoPath = $path . DIRECTORY_SEPARATOR . $relativePath . $fileName;

                if (!is_file($heoPath)) {
                    continue;
                }

                $heoData = fopen($heoPath, 'r');

                if ($heoData) {
                    while (($buffer = fgets($heoData, 4096)) !== false) {
                        if (strpos($buffer, self::$WORD_VALUE_CODE) !== false) {
                            $repeat = 4;
                            $needle = str_repeat(self::$COMMA_DELIMITER, $repeat);

                            $word = trim(substr(str_replace('?', '', $buffer), strrpos($buffer, $needle) + $repeat));
                        }
                    }
                    if (!feof($heoData)) {
                        throw new Exception('Data file parsing error');
                    }
                    fclose($heoData);
                }

                if (empty($word) || empty($transcription)) {
                    continue;
                }

                $wordItems = explode(self::$SPACE_DELIMITER, $word);
                $transcriptionItems = explode(self::$TAB_DELIMITER, $transcription);

                if (count($wordItems) !== count($transcriptionItems)) {
                    continue;
                }

                $data[] = [
                    $directory,
                    $sources,
                    $transcription,
                    $word
                ];

            }
        }

        array_unshift($data, [ self::$WORD_DIRECTORY_CODE, self::$WORD_SOURCES_CODE, self::$WORD_TRANSCRIPTION_CODE, self::$WORD_VALUE_CODE ]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($data);
        $writer = new Csv($spreadsheet);
        $writer->save(base_path() . DIRECTORY_SEPARATOR . self::$DATA_FILE);
        // save CSV
    }

    /**
     * @param $languageId
     * @param $words ([ word0 => transcription0, word1 => transcription1, ... ])
     */
    public static function updateDictionaries($languageId, $words) {
        $language = LanguageConfiguration::find($languageId);
        $dictionaryPath = sprintf('%s/model/%s/%s', Config::get('paths.pocketsphinx'), $language->language, sprintf('cmudict-%s.dict', $language->language));

        $dictionary = [];
        $reader = @fopen($dictionaryPath, 'r');
        if ($reader) {
            while (($line = fgets($reader, 4096)) !== false) {
                $dictionary[] = substr($line, 0, strpos($line, self::$SPACE_DELIMITER));
            }
            if (!feof($reader)) {
                Log::error('Dictionary not updated!');
            }
            fclose($reader);
        }

        $diff = array_diff(array_keys($words), $dictionary);

        if (empty($diff)) {
            Log::debug('NO NEW WORDS');
            return;
        }

        $data = '';
        foreach ($diff as $word) {
            $data .= $word . self::$SPACE_DELIMITER . $words[$word] . PHP_EOL;
        }

        Log::debug('NEW WORDS: ' . $data);

        // create backup
        copy($dictionaryPath, $dictionaryPath . '_old');

        if (is_writable($dictionaryPath)) {
            if (!$handle = fopen($dictionaryPath, 'a')) {
                Log::error('Dictionary not updated! Can\'t open dictionary!');
            }
            if (fwrite($handle, $data) === false) {
                Log::error('Dictionary not updated! Can\'t update dictionary...');
            }
            fclose($handle);
        } else {
            Log::error('Dictionary not updated! Permission denied!');
        }

        // delete zip with old dictionary
        $perfectModelZipPath = sprintf('%s/%s_%s.zip', PathHelper::getPerfectSpeakerAbsoluteModelPath(), AcousticModelAdapter::$map_adapt, $language->language);
        if (is_dir($perfectModelZipPath)) {
            unlink($perfectModelZipPath);
        }
    }

    public static function checkDictionaryForDiscrepancies(LanguageConfiguration $language) {
        $dictionaryPath = sprintf('%s/model/%s/%s', Config::get('paths.pocketsphinx'), $language->language, sprintf('cmudict-%s.dict', $language->language));
        $dictionary = [];
        $reader = @fopen($dictionaryPath, 'r');
        if ($reader) {
            while (($line = fgets($reader, 4096)) !== false) {
                $spacePosition = strpos($line, ' ');
                $dictionary[] = [
                    'word' => strtolower(trim(substr($line, 0, $spacePosition))),
                    'transcription' => strtoupper(trim(substr($line, $spacePosition + 1)))
                ];
            }
            fclose($reader);
        }
        $db = ('\\Progforce\\General\\Models\\' . $language->words_table)::select('word', 'transcription1', 'word_id')->distinct()->get()->map(function ($word) {
            return [
                'word' => str_replace(self::$SPACE_DELIMITER, self::$UNDERSCORE_DELIMITER, strtolower($word->word)),
                'transcription' => strtoupper($word->transcription1),
                'word_id' => $word->word_id
            ];
        });

        $result = [];
        foreach ($db as $db_word) {
            $found = false;
            foreach ($dictionary as $dict_word) {
                // count === 1 - word_id
                if (count(array_diff($db_word, $dict_word)) === 1) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $result[] = [
                    'language' => $language->language,
                    'word_id' => $db_word['word_id'],
                    'word' => $db_word['word'],
                    'transcription' => $db_word['transcription']
                ];
            }
        }

        return $result;
    }

    public static function checkDictionaryPhonemes(LanguageConfiguration $language) {
        $dictionaryPath = sprintf('%s/model/%s/%s', Config::get('paths.pocketsphinx'), $language->language, sprintf('cmudict-%s.dict', $language->language));
        $phonemes = [];
        $reader = @fopen($dictionaryPath, 'r');
        if ($reader) {
            while (($line = fgets($reader, 4096)) !== false) {
                $spacePosition = strpos($line, self::$SPACE_DELIMITER);
                $newPhonemes = explode(self::$SPACE_DELIMITER, strtoupper(trim(substr($line, $spacePosition + 1))));
                $phonemes = array_merge($phonemes, array_diff($newPhonemes, $phonemes));
            }
            fclose($reader);
        }

        return array_diff($phonemes, config('phonemes')[$language->language]);
    }

    public static function checkDBPhonemes(LanguageConfiguration $language) {
        $phonemes = [];
        $words = ('\\Progforce\\General\\Models\\' . $language->words_table)::select('word', 'transcription1', 'word_id')->distinct()->get()->map(function ($word) {
            return [
                'word' => strtolower($word->word),
                'transcription' => $word->transcription1
            ];
        });

        foreach ($words as $word) {
            $spacePosition = strpos($word['transcription'], self::$SPACE_DELIMITER);
            $newPhonemes = explode(self::$SPACE_DELIMITER, strtoupper(trim(substr($word['transcription'], $spacePosition + 1))));
            $phonemes = array_merge($phonemes, array_diff($newPhonemes, $phonemes));
        }

        return array_diff($phonemes, config('phonemes')[$language->language]);
    }

    public static function fixDictionary(LanguageConfiguration $language) {
        $dictionaryPath = sprintf('%s/model/%s/%s', config('paths.pocketsphinx'), $language->language, sprintf('cmudict-%s.dict', $language->language));

        $words = ('\\Progforce\\General\\Models\\' . $language->words_table)::select('word', 'transcription1', 'word_id')->distinct()->get()->map(function ($word) use ($language) {
            return [
                'word' => str_replace(self::$SPACE_DELIMITER, self::$UNDERSCORE_DELIMITER, strtolower(trim($word->word))),
                'transcription' => TranscriptionHelper::normalize($word->transcription1, $language->language)
            ];
        });

        $data = '';
        foreach ($words as $word) {
            $data .= $word['word'] . self::$SPACE_DELIMITER . $word['transcription'] . PHP_EOL;
        }

        // create backup
        copy($dictionaryPath, $dictionaryPath . '_before_fixing_' . date('Y-m-d_H-i-s'));

        file_put_contents($dictionaryPath, $data);

        // delete zip with old dictionary
        $perfectModelZipPath = sprintf('%s/%s_%s.zip', PathHelper::getPerfectSpeakerAbsoluteModelPath(), AcousticModelAdapter::$map_adapt, $language->language);
        if (file_exists($perfectModelZipPath)) {
            unlink($perfectModelZipPath);
        }
    }
}
