<?php namespace Progforce\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UsersAddGuestFlag extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->boolean('is_guest')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('is_guest');
        });
    }
}
