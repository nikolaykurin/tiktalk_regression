<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RefactoringProgforceGeneralSoundsTables2 extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_words', function (Blueprint $table) {
            $table->dropColumn('sound');
        });

        Schema::table('progforce_general_words_he_il', function (Blueprint $table) {
            $table->dropColumn('sound');
        });

        Schema::table('progforce_general_patient_treatment_plans', function (Blueprint $table) {
            $table->dropColumn('sound');
        });
    }


}