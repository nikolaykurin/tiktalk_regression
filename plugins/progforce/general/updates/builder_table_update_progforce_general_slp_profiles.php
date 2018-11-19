<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceGeneralSlpProfiles extends Migration
{
    public function up()
    {
        Schema::table('progforce_general_slp_profiles', function($table)
        {
            $table->string('user', 30);
            $table->string('pass', 15);
            $table->dateTime('start_date')->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasTable('progforce_general_slp_profiles')) {
            Schema::table('progforce_general_slp_profiles', function($table)
            {
                $table->dropColumn('user');
                $table->dropColumn('pass');
                $table->dropColumn('start_date');
            });
        }
    }
}
