<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class ChangeWordsTranscription1ColLength extends Migration
{
    public function up()
    {
        Schema::table('progforce_general_words', function ($table) {
            $table->string('transcription1', 200)->change();
        });

        Schema::table('progforce_general_words_he_il', function ($table) {
            $table->string('transcription1', 200)->change();
        });
    }

    public function down()
    {
    }
}
