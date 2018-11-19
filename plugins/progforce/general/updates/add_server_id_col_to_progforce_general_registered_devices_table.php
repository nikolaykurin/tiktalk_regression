<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddServerIdColToProgforceGeneralRegisteredDevicesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->smallInteger('server_id')->unsigned()->after('device_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->dropColumn('server_id');
        });
    }

}