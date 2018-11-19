<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateProgforceGeneralTreatmentPlansPhases extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_treatment_plans_phases', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->unsignedInteger('plan_id');
            $table->unsignedInteger('phase_id')->default(0);
            $table->smallInteger('row_num')->unsigned()->default(0);
            $table->unsignedInteger('phase_status_id')->default(1);
            $table->date('phase_status_date')->nullable()->default(null);
        });
    }

    public function down()
    {
        Schema::dropIfExists('progforce_general_treatment_plans_phases');
    }
}
