<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceGeneralLocationWithinWordAux extends Migration
{
    public function up()
    {
        Schema::rename('progforce_general_parts_of_speech', 'progforce_general_location_within_word_aux');
    }
    
    public function down()
    {
        Schema::rename('progforce_general_location_within_word_aux', 'progforce_general_parts_of_speech');
    }
}
