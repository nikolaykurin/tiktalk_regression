<?php namespace Progforce\General\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class PrerecordedWord extends Model
{
    use Validation;

    public $table = 'progforce_general_prerecorded_words';

    public $timestamps = false;

    public $rules = [

    ];

    public $belongsTo = [

    ];

    protected $fillable = [

    ];

    public function getAsset() {
//        $wordsTable = '\\Progforce\\General\\Models\\'.$user->language->words_table;
//        $wordsTable = new $wordsTable();
    }
}
