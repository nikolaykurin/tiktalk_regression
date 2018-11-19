<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralTreatmentPhases extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_treatment_phases', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('description');
            $table->integer('number_of_syllables')->unsigned();
            $table->text('intonation');
            $table->text('location_within_word');
            $table->text('segment_location_within_phoneme');
            $table->integer('complexity_id');
            $table->integer('part_of_speech_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_treatment_phases');
    }
}
