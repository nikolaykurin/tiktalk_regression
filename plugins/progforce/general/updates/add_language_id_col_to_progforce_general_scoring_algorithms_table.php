<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddLanguageIdColToProgforceGeneralScoringAlgorithmsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_scoring_algorithms', function (Blueprint $table) {
            $table->smallInteger('language_id')->unsigned()->default(1)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('progforce_general_scoring_algorithms', function (Blueprint $table) {
            $table->dropColumn('language_id');
        });
    }

}