<?php namespace Progforce\User\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersAddBirthDateField extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->date('birth_date')->after('last_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn([
                'birth_date',
            ]);
        });
    }
}
