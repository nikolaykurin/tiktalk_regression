<?php namespace Progforce\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UsersAddRegisteredDeviceId extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->integer('registered_device_id')->unsigned()->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('registered_device_id');
        });
    }
}
