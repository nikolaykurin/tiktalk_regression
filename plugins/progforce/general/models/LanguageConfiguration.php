<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use Illuminate\Support\Facades\Config;
use Progforce\General\Classes\Helpers\PathHelper;
use Exception;
use October\Rain\Exception\ValidationException;

class LanguageConfiguration extends Model
{
    use Validation;

    public $table = 'progforce_general_language_configurations';

    public $timestamps = false;

    public $rules = [
        'language' => 'required|unique:progforce_general_language_configurations',
        'path_to_images' => 'required|alpha',
        'path_to_recordings' => 'required|different:path_to_images|alpha',
        'words_table' => 'required',
    ];

    public static function getLangCode($id) {
        $langs = Config::get('languages');
        if (!$langs) { return null; }
        $lang = array_get($langs, $id, $langs[1]);
        return $lang ? $lang['code'] : null;
    }

    public static function getLangIdByCode($code) {
        $ids = array_column(Config::get('languages'), 'id', 'code');
        $id = array_get($ids, $code);
        if ($id) {
            return $id;
        } else {
            throw new Exception("Wrong langCode identificator - $code!");
        }
    }

    public static function getWordModel($langId) {
        $langs = Config::get('languages');
        $lang = array_get($langs, $langId);
        if ($lang) {
            return 'Progforce\\General\\Models\\' . $lang['wordModel'];
        } else {
            throw new Exception("Wrong langId identificator - $langId!");
        }
    }

    public static function getWordModelByCode($langCode) {
        $models = array_column(Config::get('languages'), 'wordModel', 'code');
        $langModel = array_get($models, $langCode);
        if ($langModel) {
            return 'Progforce\\General\\Models\\' . $langModel;
        } else {
            throw new Exception("Wrong langCode identificator - $langCode!");
        }
    }

    public function getLanguageOptions() {
        $options = array_column(Config::get('languages'), 'name', 'id');
        return $options;
    }

    public function afterCreate() {
        try {
            $languageAbsolutePath = PathHelper::getLanguageAbsolutePath($this->id);

            mkdir(sprintf('%s/%s', $languageAbsolutePath, $this->path_to_images), 0777, true);
            mkdir(sprintf('%s/%s', $languageAbsolutePath, $this->path_to_recordings), 0777, true);
        } catch (Exception $e) {
            throw new ValidationException(['Can\'t create necessary folders!']);
        }
    }
}