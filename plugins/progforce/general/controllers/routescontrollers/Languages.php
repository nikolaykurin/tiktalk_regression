<?php namespace Progforce\General\Controllers\RoutesControllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\Helpers\PathHelper;
use Illuminate\Support\Facades\Redirect;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Progforce\General\Models\GenConfig;
use Progforce\General\Models\LanguageConfiguration;

class Languages extends Controller {

    public function generateDictionaries(Request $request) {
        $languages = LanguageConfiguration::all();
        $genConfig = GenConfig::get();

        foreach ($languages as $language) {
            $res = [];

            $wordsTable = '\\Progforce\\General\\Models\\' . $language->words_table;
            $words = $wordsTable::all('word_id', 'word');

            foreach ($words as $word) {
                $res[$word->word_id] = $word->word;
            }

            $langCode = explode('-', $language->language)[0];
            if ($genConfig) {
                $res += $genConfig->getWords($langCode);
            }
            file_put_contents(sprintf('%s/%s', PathHelper::getFilesPath(), Config::get('constants.assets_parser')[$langCode]), json_encode($res));
        }

        return Redirect::to('/');
    }

    public function downloadLangFile(Request $request) {
        $lang = $request->input('lang');

        switch ($lang) {
            case 'en':
                $file = Config::get('constants.assets_parser.en');
                break;
            case 'he':
                $file = Config::get('constants.assets_parser.he');
                break;
            default:
                return response('Wrong \'lang\' parameter');
        }

        $path = sprintf('%s/%s', PathHelper::getFilesPath(), $file);

        return response()->download($path);
    }

    public function uploadLangFile(Request $request) {
        $assets = $request->file('assets');

        $spreadsheet = IOFactory::load($assets);
        $worksheet = $spreadsheet->getActiveSheet();

        $en = [];
        $he = [];
        foreach ($worksheet->getRowIterator() AS $row) {

            $row_index = $row->getRowIndex();
            $key_val = $worksheet->getCellByColumnAndRow(1, $row_index)->getValue();
            $en_val = $worksheet->getCellByColumnAndRow(2, $row_index)->getValue();
            $he_val = $worksheet->getCellByColumnAndRow(3, $row_index)->getValue();

            if (!empty($en_val)) {
                $en[$key_val] = is_string($en_val) ? $en_val : $en_val->getPlainText();
            }
            if (!empty($he_val)) {
                $he[$key_val] = $he_val;
            }
        }

        $genConfig = GenConfig::get();
        if ($genConfig) {
            $en = $genConfig->getWords('en') + $en;
            $he = $genConfig->getWords('he') + $he;
        }

        file_put_contents(sprintf('%s/%s', PathHelper::getFilesPath(), Config::get('constants.assets_parser.en-us')), json_encode($en));
        file_put_contents(sprintf('%s/%s', PathHelper::getFilesPath(), Config::get('constants.assets_parser.he-il')), json_encode($he));

        return Redirect::to('/');
    }

    private function parseWords($code) {

    }
}