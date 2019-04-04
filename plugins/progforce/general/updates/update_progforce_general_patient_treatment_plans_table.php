<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UpdateProgforceGeneralPatientTreatmentPlansTable extends Migration {

    public function up() {
        Schema::table('progforce_general_patient_treatment_plans', function (Blueprint $table) {
            $table->boolean('is_multisound')->default(false);
        });
    }

    public function down() {
        //
    }

}
