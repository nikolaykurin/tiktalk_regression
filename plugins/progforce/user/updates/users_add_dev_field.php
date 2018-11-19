<?php namespace Progforce\User\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersAddDevField extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->boolean('developer_mode')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn([
                'developer_mode',
            ]);
        });
    }
}
