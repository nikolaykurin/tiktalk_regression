<?php namespace Progforce\General\Models;

use Backend\Models\ImportModel;
use Progforce\General\Models\PrerecordedWord;
use Exception;

class PrerecordedWordsImport extends ImportModel {

    public $rules = [
        //
    ];

    public function importData($results, $sessionKey = null) {

        if (!empty($results)) {
            PrerecordedWord::truncate();
        }

        foreach ($results as $row => $data) {

            try {
                $prerecordedWord = new PrerecordedWord();
                $prerecordedWord->asset_id = $data['asset_id'];
                $prerecordedWord->text = $data['text'];
                $prerecordedWord->save();

                $this->logCreated();
            }
            catch (Exception $e) {
                $this->logError($row, $e->getMessage());
            }

        }

    }

}
