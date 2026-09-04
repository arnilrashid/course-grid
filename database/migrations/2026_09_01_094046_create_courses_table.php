<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the instructor who owns this course
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique(); // used in the course URL, e.g. /courses/laravel-for-beginners
            $table->text('description')->nullable();
            $table->string('language', 10)->default('en'); // ISO locale code, e.g. 'en', 'fr', 'es'
            $table->decimal('price', 8, 2)->default(0); // e.g. 49.99
            $table->string('status')->default('draft'); // draft | published
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
