<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RefactoringProgforceGeneralSoundsTables extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_words', function (Blueprint $table) {
            $table->integer('sound_id')->unsigned()->after('word_id');
        });

        Schema::table('progforce_general_words_he_il', function (Blueprint $table) {
            $table->integer('sound_id')->unsigned()->after('word_id');
        });

        Schema::table('progforce_general_patient_treatment_plans', function (Blueprint $table) {
            $table->integer('sound_id')->unsigned()->after('user_id');
        });

        Schema::dropIfExists('progforce_general_treatment_sounds_en_us');
        Schema::dropIfExists('progforce_general_treatment_sounds_he_il');
    }


}