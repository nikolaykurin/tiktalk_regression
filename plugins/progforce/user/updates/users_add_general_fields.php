<?php namespace Progforce\User\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersAddGeneralFields extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn([
                'email',
                'password',
                'name',
                'username',
                'surname'
            ]);

            $table->integer('code')->unsigned();
            $table->string('first_name');
            $table->integer('slp_id')->unsigned();
            $table->text('slp_first_name');
            $table->integer('age');
            $table->integer('country_id')->unsigned();
            $table->integer('language_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn([
                'first_name',
                'slp_id',
                'slp_first_name',
                'age',
                'country_id',
                'language_id'
            ]);
        });
    }
}
