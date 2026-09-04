<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete(); // which section this lesson belongs to
            $table->string('title');
            $table->string('type')->default('video'); // video | text | quiz | resource
            $table->text('content')->nullable(); // video URL, text body, or file path depending on type
            $table->unsignedInteger('position')->default(0); // controls lesson order within the section
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
