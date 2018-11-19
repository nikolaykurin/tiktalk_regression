<?php namespace Progforce\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceUserSLPS extends Migration
{
    public function up()
    {
        Schema::table('progforce_user_s_l_p_s', function($table)
        {
            $table->string('persist_code')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('progforce_user_s_l_p_s', function($table)
        {
            $table->dropColumn('persist_code');
        });
    }
}
