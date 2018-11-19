<?php namespace Progforce\General\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateProgforceGeneralIssueCodes extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_issue_codes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('issue_id');
            $table->text('description')->nullable();
            $table->string('message');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_issue_codes');
    }
}
