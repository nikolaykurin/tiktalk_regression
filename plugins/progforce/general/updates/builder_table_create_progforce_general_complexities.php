<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralComplexities extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_complexities', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('description');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_complexities');
    }
}
