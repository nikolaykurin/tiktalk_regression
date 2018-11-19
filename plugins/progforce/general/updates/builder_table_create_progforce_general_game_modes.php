<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralGameModes extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_game_modes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('description', 100);
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_general_game_modes');
    }
}
