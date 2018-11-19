<?php namespace Progforce\General\Controllers;

use BackendMenu;
use Progforce\General\Controllers\BaseWords;
use Progforce\General\Classes\Helpers\ListFilterHelper;
use Progforce\General\Classes\WordsImport;
use Progforce\General\Models\WordHeIl;

/**
 * Words Back-end Controller
 */
class WordsHeIl extends BaseWords
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

        BackendMenu::setContext('Progforce.General', 'general', 'wordsheil');
    }

    public function listFilterExtendScopes($filter) {
        $scopes = ListFilterHelper::getScopes(WordHeIl::class);

        $filter->addScopes($scopes);
    }

    public function onImport() {
        WordsImport::importWords('words_he_il.xlsx', 'he-il');
        return redirect('/backend/progforce/general/wordsheil');
    }

    public static function import() {
//        WordsImport::import('FullWordListForDB.xlsx', 'cmudict-en-us.dict');
//        WordsImport::import('FullWordListForDB.xls', 'cmudict-en-us.dict');
        return redirect('/backend/progforce/general/words');
    }

    public function onGetFileHe() {
        return redirect('/download-he');
//        WordsImport::downloadWordsEn();
    }

    public function loadFileHe() {
        WordsImport::downloadWordsHe();
    }
}
