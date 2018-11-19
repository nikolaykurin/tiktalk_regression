<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateProgforceGeneralIntonationTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_intonations', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 15);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_intonations');
    }
}
