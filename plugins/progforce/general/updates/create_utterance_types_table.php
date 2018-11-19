<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateUtteranceTypesTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_utterance_types', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('name', 50);
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_general_utterance_types');
    }
}
