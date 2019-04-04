<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateParentsTable extends Migration {

    public function up() {
        Schema::create('progforce_general_parents', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('username');
            $table->string('email');
            $table->string('password');
        });
    }

    public function down() {
        Schema::dropIfExists('progforce_general_parents');
    }

}
