<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddTimestampsToLogsAndSessionsTables extends Migration {

    public function up() {
        Schema::table('progforce_general_logs', function (Blueprint $table) {
            $table->timestamps();
        });
        Schema::table('progforce_general_sessions', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down() {
        Schema::table('progforce_general_logs', function (Blueprint $table) {
            $table->dropColumn([
                'created_at',
                'updated_at'
            ]);
        });
        Schema::table('progforce_general_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'created_at',
                'updated_at'
            ]);
        });
    }

}
