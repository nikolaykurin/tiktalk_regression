<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateResultsTable extends Migration {

    public function up() {
        Schema::create('progforce_general_results', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->dateTime('treatment_started_at');
            $table->dateTime('treatment_finished_at')->nullable();
            $table->integer('treatment_duration')->nullable();
            $table->float   ('treatment_complexity');
            $table->integer('treatment_phases_count');
            $table->integer('patient_age')->nullable();
            $table->float('patient_gender');
            $table->boolean('is_real');
        });
    }

    public function down() {
        Schema::dropIfExists('progforce_general_results');
    }
}
