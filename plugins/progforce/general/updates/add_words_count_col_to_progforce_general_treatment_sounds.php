<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddWordsCountColToProgforceGeneralTreatmentSoundsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_treatment_sounds', function (Blueprint $table) {
            $table->smallInteger('words_count')->unsigned()->after('sound')->default(30);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('progforce_general_treatment_sounds', function (Blueprint $table) {
            $table->dropColumn('words_count');
        });
    }

}