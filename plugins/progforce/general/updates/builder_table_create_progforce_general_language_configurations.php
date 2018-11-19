<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralLanguageConfigurations extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_language_configurations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('language');
            $table->text('path_to_images');
            $table->text('path_to_recordings');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_language_configurations');
    }
}
