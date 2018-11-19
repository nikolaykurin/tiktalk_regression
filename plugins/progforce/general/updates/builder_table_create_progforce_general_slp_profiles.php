<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralSlpProfiles extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_slp_profiles', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('country_id')->unsigned();
            $table->integer('language_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_slp_profiles');
    }
}
