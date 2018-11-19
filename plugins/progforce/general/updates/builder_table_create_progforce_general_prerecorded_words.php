<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforcePrerecordedWords extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_prerecorded_words', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('asset_id')->unsigned();
            $table->string('text')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_general_prerecorded_words');
    }
}