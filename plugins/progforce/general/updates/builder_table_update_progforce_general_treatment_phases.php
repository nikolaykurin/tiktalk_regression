<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceGeneralTreatmentPhases extends Migration
{
    public function up()
    {
        Schema::table('progforce_general_treatment_phases', function($table)
        {
            $table->integer('game_mode_id')->nullable();
            $table->renameColumn('part_of_speech_id', 'function_within_phoneme_id');
            $table->integer('part_of_speech');
            $table->dropColumn('segment_location_within_phoneme');
            $table->string('number_of_syllables', 20)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('progforce_general_treatment_phases', function($table)
        {
            $table->dropColumn('game_mode_id');
        });
    }
}
