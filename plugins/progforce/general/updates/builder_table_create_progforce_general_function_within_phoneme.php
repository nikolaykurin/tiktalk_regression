<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralFunctionWithinPhoneme extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_function_within_phoneme', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('function', 50);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_function_within_phoneme');
    }
}
