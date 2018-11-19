<?php namespace Progforce\General\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class BuilderTableDropProgforceGeneralSlpProfiles extends Migration
{
    public function up()
    {
        Schema::dropIfExists('progforce_general_slp_profiles');
    }

    public function down()
    {
        //
    }
}
