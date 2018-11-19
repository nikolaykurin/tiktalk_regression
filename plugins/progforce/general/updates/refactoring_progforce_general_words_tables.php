<?php

use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RefactoringProgforceGeneralWordsTables extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('progforce_general_words', function (Blueprint $table) {
            $table->dropColumn([
                'image_1', 'image_2', 'image_3', 'image_4', 'image_5', 
                'audio_1', 'audio_2', 'audio_3', 'audio_4', 'audio_5'
            ]);
            $table->boolean('has_audio')->default(false)->after('part_of_speech_id');
        });

        Schema::table('progforce_general_words_he_il', function (Blueprint $table) {
            $table->dropColumn([
                'image_1', 'image_2', 'image_3', 'image_4', 'image_5', 
                'audio_1', 'audio_2', 'audio_3', 'audio_4', 'audio_5'
            ]);
            $table->boolean('has_audio')->default(false)->after('part_of_speech_id');
        });
    }


}