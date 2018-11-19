<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceGeneralLanguageConfigurations2 extends Migration
{
    public function up()
    {
        Schema::table('progforce_general_language_configurations', function($table)
        {
            $table->renameColumn('table', 'words_table');
        });
    }
    
    public function down()
    {
        Schema::table('progforce_general_language_configurations', function($table)
        {
            $table->renameColumn('words_table', 'table');
        });
    }
}
