<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddRowNumColToProgforceGeneralPatientTreatmentPlansTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_patient_treatment_plans', function (Blueprint $table) {
            $table->smallInteger('row_num')->unsigned()->after('user_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('progforce_general_patient_treatment_plans', function (Blueprint $table) {
            $table->dropColumn('row_num');
        });
    }

}