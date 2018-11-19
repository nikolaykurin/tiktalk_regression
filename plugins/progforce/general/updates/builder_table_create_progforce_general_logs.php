<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateLogsTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_logs', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->dateTime('datetime');
            $table->integer('action')->unsigned();
            $table->json('data')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_general_logs');
    }
}
