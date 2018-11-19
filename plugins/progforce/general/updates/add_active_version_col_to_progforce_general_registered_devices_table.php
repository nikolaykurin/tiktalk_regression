<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddActiveVersionColToProgforceGeneralRegisteredDevicesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->string('active_version', 256)->after('mixed_id')->nullable()->default('Not Installed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->dropColumn('active_version');
        });
    }

}
