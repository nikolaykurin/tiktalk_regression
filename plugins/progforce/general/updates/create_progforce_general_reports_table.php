<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateProgforceGeneralReportsTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_reports', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->smallInteger('game_id')->unsigned();
            $table->smallInteger('result_id')->unsigned();
            $table->string('folder_name', 60);
            $table->string('file_name', 25);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_reports');
    }
}
