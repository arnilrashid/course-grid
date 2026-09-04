<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_bundle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_bundle_id', 'course_id']); // a course can't be added to the same bundle twice
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_courses');
    }
};
