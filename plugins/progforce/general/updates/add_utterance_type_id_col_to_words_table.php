<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddUtteranceTypeIdColToWordsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_words', function (Blueprint $table) {
            $table->integer('utterance_type_id')->unsigned()->after('complexity_id')->nullable()->default(1);
            $table->tinyInteger('sound_occurrences')->unsigned()->after('sound_id')->nullable();
        });

        Schema::table('progforce_general_words_he_il', function (Blueprint $table) {
            $table->integer('utterance_type_id')->unsigned()->after('complexity_id')->nullable()->default(1);
            $table->tinyInteger('sound_occurrences')->unsigned()->after('sound_id')->nullable();
        });

        Schema::table('progforce_general_treatment_phases', function (Blueprint $table) {
            $table->integer('utterance_type_id')->unsigned()->after('complexity_id')->nullable();
            $table->tinyInteger('sound_occurrences')->unsigned()->after('utterance_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('progforce_general_words', function (Blueprint $table) {
            $table->dropColumn('utterance_type_id');
            $table->dropColumn('sound_occurrences');
        });

        Schema::table('progforce_general_words_he_il', function (Blueprint $table) {
            $table->dropColumn('utterance_type_id');
            $table->dropColumn('sound_occurrences');
        });

        Schema::table('progforce_general_treatment_phases', function (Blueprint $table) {
            $table->dropColumn('utterance_type_id');
            $table->dropColumn('sound_occurrences');
        });
    }

}