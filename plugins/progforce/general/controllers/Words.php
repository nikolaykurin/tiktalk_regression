<?php namespace Progforce\General\Controllers;

use BackendMenu;
use \Illuminate\Http\Request;
use Progforce\General\Classes\Helpers\MediaHelper;
use Progforce\General\Classes\Helpers\GoogleDriveHelper;
use Progforce\General\Classes\Helpers\ListFilterHelper;
use Progforce\General\Classes\WordsImport;
use Progforce\General\Controllers\BaseWords;
use Progforce\General\Models\FunctionWithinPhoneme;
use Progforce\General\Models\LocationWithinWordAux;
use Progforce\General\Models\Word;
use Progforce\General\Models\LanguageConfiguration;

/**
 * Words Back-end Controller
 */
class Words extends BaseWords
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Progforce.General', 'general', 'words');
    }

    public function listFilterExtendScopes($filter) {
        $scopes = ListFilterHelper::getScopes(Word::class);

        $filter->addScopes($scopes);
    }

    public function onImport() {
        WordsImport::importWords('words_en_us.xlsx', 'en-us');
        return redirect('/backend/progforce/general/words');
    }

    public function updateFields() {
        $words = Word::all();

        foreach ($words as $word) {
            $location = LocationWithinWordAux::where('description', $word->location_within_word)->first();
            $function = FunctionWithinPhoneme::where('function', $word->segment_location_within_phoneme)->first();
            $word->location_within_word_id = $location->id;
            $word->segment_location_within_phoneme_id = $function->id;
            $word->save();
        }
    }

    public function getMedia(Request $request) {
        if (!$request->has('codes') || empty($codes = $request->input('codes'))) {
            return response('\'codes\' field requires');
        }
traceLog('---codes---');
traceLog($codes);
        if (!$request->has('lang_id')) {
            return response('Language Id field requires');
        }
        $langId = $request->input('lang_id');

        $langCode = LanguageConfiguration::getLangCode($langId);
        if (!$langCode) {
            return response('Language not found!');
        }

        $wordsCodes = json_decode($codes);
        return response()->download(MediaHelper::getWordsMedia($wordsCodes, $langCode));
    }

    public function renameAudio() {
        return GoogleDriveHelper::renameAudio();
    }

    public function onGetFileEn() {
        return redirect('/download-en');
//        WordsImport::downloadWordsEn();
    }

    public function loadFileEn() {
        WordsImport::downloadWordsEn();
    }

    public function onReportEn() {
        return redirect('/report-sound-count');
    }
}


