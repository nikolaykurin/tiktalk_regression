<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateProgforceGeneralPhonemeTable extends Migration
{
    public function up()
    {
        Schema::create('progforce_general_phonemes', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 5);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('progforce_general_phonemes');
    }
}
