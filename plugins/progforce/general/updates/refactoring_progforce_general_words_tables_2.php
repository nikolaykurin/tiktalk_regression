<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RefactoringProgforceGeneralWordsTables2 extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_words', function (Blueprint $table) {
            $table->boolean('has_image')->default(false)->after('has_audio');
        });

        Schema::table('progforce_general_words_he_il', function (Blueprint $table) {
            $table->boolean('has_image')->default(false)->after('has_audio');
        });
    }


}