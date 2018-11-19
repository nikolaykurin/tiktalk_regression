<?php namespace Progforce\User\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSLPSTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_user_s_l_p_s', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->smallInteger('gender');
            $table->text('address')->nullable();
            $table->json('languages');
            $table->string('picture')->nullable();
            $table->string('user_name');
            $table->string('password');
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_user_s_l_p_s');
    }
}
