<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddIdsColsToProgforceGeneralRegisteredDevicesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->string('imei_id', 50)->after('device_id')->nullable();
            $table->string('firebase_id', 50)->after('imei_id')->nullable();
            $table->string('mac_id', 50)->after('firebase_id')->nullable();
            $table->string('android_id', 50)->after('mac_id')->nullable();
            $table->string('unity_id', 50)->after('android_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
    }

}