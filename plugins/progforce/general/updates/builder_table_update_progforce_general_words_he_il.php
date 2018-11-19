<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceGeneralWordsHeIl extends Migration
{
    public function up()
    {
        Schema::table('progforce_general_words_he_il', function($table)
        {
            $table->integer('location_within_word_id')->nullable();
            $table->integer('segment_location_within_phoneme_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('progforce_general_words_he_il', function($table)
        {
            $table->dropColumn('location_within_word_id');
            $table->dropColumn('segment_location_within_phoneme_id');
        });
    }
}
