<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->restrictOnDelete();
            $table->decimal('revenue_share_percentage', 5, 2);
            $table->timestamps();

            $table->unique(['course_id', 'instructor_id']);
        });
    }
    public function down() {
        Schema::dropIfExists('course_instructors');
    }

};
