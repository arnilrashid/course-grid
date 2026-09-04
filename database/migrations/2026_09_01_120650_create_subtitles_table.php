<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtitles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('language', 10); // ISO locale code, e.g. 'en', 'fr'
            $table->string('file_path'); // path to the .vtt caption file
            $table->timestamps();

            $table->unique(['lesson_id', 'language']); // one subtitle file per language per lesson
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtitles');
    }
};
