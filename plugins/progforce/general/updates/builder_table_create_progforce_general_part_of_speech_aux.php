<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralPartOfSpeechAux extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_part_of_speech_aux', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_part_of_speech_aux');
    }
}
