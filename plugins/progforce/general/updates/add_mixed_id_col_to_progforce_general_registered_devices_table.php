<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddMixedIdColToProgforceGeneralRegisteredDevicesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->string('mixed_id', 50)->after('unity_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->dropColumn('mixed_id');
        });
    }

}
