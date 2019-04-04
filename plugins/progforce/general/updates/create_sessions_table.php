<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSessionsTable extends Migration {

    public function up() {
        Schema::create('progforce_general_sessions', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->dateTime('datetime_start')->nullable();
            $table->dateTime('datetime_end')->nullable();
        });
    }

    public function down() {
        Schema::dropIfExists('progforce_general_sessions');
    }

}
