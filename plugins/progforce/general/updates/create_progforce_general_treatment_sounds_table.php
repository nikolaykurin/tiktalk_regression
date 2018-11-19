<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProgforceGeneralTreatmentSounds extends Migration
{
    public function up() {
        Schema::create('progforce_general_treatment_sounds', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->smallInteger('language_id')->unsigned();
            $table->string('sound', 10);
        });
    }

    public function down() {
        Schema::dropIfExists('progforce_general_treatment_sounds');
    }
}
