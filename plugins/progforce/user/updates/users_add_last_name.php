<?php namespace Progforce\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UsersAddLastName extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->string('last_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('last_name');
        });
    }
}
