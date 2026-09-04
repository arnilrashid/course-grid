<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('not_started'); // not_started | in_progress | completed
            $table->unsignedTinyInteger('progress_percent')->default(0); // for partial video progress, e.g. 73
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'lesson_id']); // one progress row per student per lesson
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};
