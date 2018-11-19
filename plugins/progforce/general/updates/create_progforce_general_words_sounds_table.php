<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateProgforceGeneralWordsSoundsTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_words_sounds', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('word_id')->unsigned();
            $table->integer('sound_id')->unsigned();
            $table->integer('phoneme_id')->unsigned()->nullable();
            $table->integer('number_of_syllables')->unsigned();
            $table->integer('intonation_id')->unsigned()->nullable();
            $table->text('location_within_word')->nullable();
            $table->text('segment_location_within_phoneme')->nullable();
            $table->integer('complexity_id')->unsigned()->nullable();
            $table->integer('part_of_speech_id')->unsigned()->nullable();
            $table->integer('location_within_word_id')->unsigned()->nullable();
            $table->integer('segment_location_within_phoneme_id')->unsigned()->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_words_sounds');
    }
}
