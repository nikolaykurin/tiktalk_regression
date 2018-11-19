<?php namespace Progforce\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateProgforceUserSLPS_2 extends Migration
{
    public function up()
    {
        Schema::table('progforce_user_s_l_p_s', function($table)
        {
            $table->dropColumn('picture');
        });
    }
}
