<?php namespace Progforce\General\Console;

use Illuminate\Console\Command;
use Progforce\General\Classes\Helpers\AcousticModelHelper;
use Progforce\General\Classes\Helpers\PathHelper;
use Progforce\General\Models\LanguageConfiguration;
use Illuminate\Support\Facades\Config;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class CheckDictionaries extends Command {

    protected $name = 'progforce:check-dictionaries';

    protected $description = 'Check DB and dictionaries for discrepancies';

    public function handle() {
        $langs = LanguageConfiguration::all();

        $result = [];
        foreach ($langs as $lang) {
            $result = array_merge($result, AcousticModelHelper::checkDictionaryForDiscrepancies($lang));
        }

        foreach ($result as $item) {
            $this->output->error(json_encode($item));
        }

        $resultPath = PathHelper::getBaseTempPath() . DIRECTORY_SEPARATOR . sprintf('%s.csv', date('Y-m-d_H-i-s'));
        array_unshift($result, [ 'Language', 'Word ID', 'Word', 'Transcription' ]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($result);
        $writer = new Csv($spreadsheet);
        $writer->save($resultPath);

        $this->output->writeln('Stored to: ' . $resultPath);
        $this->output->writeln('Done!');
    }

}
