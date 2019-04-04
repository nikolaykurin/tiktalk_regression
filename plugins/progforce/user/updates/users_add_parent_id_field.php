<?php namespace Progforce\User\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersAddParentIdField extends Migration {

    public function up() {
        Schema::table('users', function(Blueprint $table) {
            $table->integer('parent_id')->after('birth_date')->nullable();
        });
    }

    public function down() {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn([
                'parent_id'
            ]);
        });
    }

}
