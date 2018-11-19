<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddWhiteLabelIdColToProgforceGeneralRegisteredDevicesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->smallInteger('white_label_id')->unsigned()->after('server_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('progforce_general_registered_devices', function (Blueprint $table) {
            $table->dropColumn('white_label_id');
        });
    }

}