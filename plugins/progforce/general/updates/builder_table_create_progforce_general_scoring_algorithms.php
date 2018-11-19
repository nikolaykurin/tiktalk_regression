<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralScoringAlgorithms extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_scoring_algorithms', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('field');
            $table->float('value');
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_general_scoring_algorithms');
    }
}
