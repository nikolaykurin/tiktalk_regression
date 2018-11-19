<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateProgforceGeneralWordsTable extends Migration
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
            $table->boolean('has_image')->default(false);
            $table->boolean('has_audio')->default(false);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_words');
    }
}
