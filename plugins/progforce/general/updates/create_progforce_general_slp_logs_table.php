<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSLPLogsTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_slp_logs', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('slp_id')->unsigned();
            $table->integer('plan_id')->unsigned();
            $table->smallInteger('action_id')->unsigned();
            $table->string('description', 200);
            $table->string('slp_comment', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_general_slp_logs');
    }
}
