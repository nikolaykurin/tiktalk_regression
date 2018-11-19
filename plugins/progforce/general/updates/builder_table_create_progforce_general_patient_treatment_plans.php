<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralPatientTreatmentPlans extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_patient_treatment_plans', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('protocol_sequence');
            $table->integer('protocol_status');
            $table->text('sound');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_patient_treatment_plans');
    }
}
