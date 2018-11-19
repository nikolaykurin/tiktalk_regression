<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralWords extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_words', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('language_id');
            $table->text('word');
            $table->text('word_id');
            $table->text('sound')->nullable();
            $table->text('phoneme')->nullable();
            $table->integer('number_of_syllables')->unsigned();
            $table->text('intonation');
            $table->text('location_within_word')->nullable();
            $table->text('segment_location_within_phoneme')->nullable();
            $table->integer('complexity_id')->unsigned()->nullable();
            $table->integer('part_of_speech_id')->unsigned()->nullable();
            $table->integer('location_within_word_id')->unsigned()->nullable();
            $table->integer('segment_location_within_phoneme_id')->unsigned()->nullable();
            $table->string('image_1');
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();
            $table->string('image_4')->nullable();
            $table->string('image_5')->nullable();
            $table->string('audio_1');
            $table->string('audio_2')->nullable();
            $table->string('audio_3')->nullable();
            $table->string('audio_4')->nullable();
            $table->string('audio_5')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_words');
    }
}
