<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateRegisteredTabletsTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_registered_devices', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('device_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_general_registered_devices');
    }
}
