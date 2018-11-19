<?php namespace Progforce\User\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersAddActiveField extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->boolean('is_active')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn([
                'is_active',
            ]);
        });
    }
}
