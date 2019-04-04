<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UpdateProgforceGeneralReportsTable extends Migration {

    public function up() {
        Schema::table('progforce_general_reports', function (Blueprint $table) {
            $table->smallInteger('result_id')->unsigned(false)->change();
        });
    }

    public function down() {
        //
    }

}
