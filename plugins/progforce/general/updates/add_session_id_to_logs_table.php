<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddSessionIDToLogsTable extends Migration {

    public function up() {
        Schema::table('progforce_general_logs', function (Blueprint $table) {
            $table->integer('session_id')->unsigned()->nullable();
        });
    }

    public function down() {
        Schema::table('progforce_general_logs', function (Blueprint $table) {
            $table->dropColumn('session_id');
        });
    }

}
