<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralPartsOfSpeech extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_parts_of_speech', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('description');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_parts_of_speech');
    }
}
