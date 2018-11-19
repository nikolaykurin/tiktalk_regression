<?php namespace Progforce\User\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersAddDeviceIdField extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->text('device_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn([
                'device_id',
            ]);
        });
    }
}
