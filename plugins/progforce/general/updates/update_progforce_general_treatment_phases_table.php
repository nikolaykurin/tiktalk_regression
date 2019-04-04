<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UpdateProgforceGeneralTreatmentPhasesTable extends Migration {

    public function up() {
        Schema::table('progforce_general_treatment_phases', function (Blueprint $table) {
            $table->string('sound_occurrences')->nullable()->change();
        });
    }

    public function down() {
        //
    }

}
