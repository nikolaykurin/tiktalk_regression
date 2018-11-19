<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceGeneralWords extends Migration
{
    public function up()
    {
        Schema::table('progforce_general_words', function($table)
        {
            $table->string('transcription1', 100)->nullable();
            $table->string('transcription2', 100)->nullable();
            $table->string('transcription3', 100)->nullable();
            $table->string('transcription4', 100)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('progforce_general_words', function($table)
        {
            $table->dropColumn('transcription1');
            $table->dropColumn('transcription2');
            $table->dropColumn('transcription3');
            $table->dropColumn('transcription4');
        });
    }
}
