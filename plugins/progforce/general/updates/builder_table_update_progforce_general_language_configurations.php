<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceGeneralLanguageConfigurations extends Migration
{
    public function up()
    {
        Schema::table('progforce_general_language_configurations', function($table)
        {
            $table->string('table');
        });
    }
    
    public function down()
    {
        Schema::table('progforce_general_language_configurations', function($table)
        {
            $table->dropColumn('table');
        });
    }
}
