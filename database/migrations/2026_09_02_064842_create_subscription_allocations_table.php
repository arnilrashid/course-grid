<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('subscription_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_period_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->restrictOnDelete();
            $table->decimal('share_percentage_snapshot', 5, 2);
            $table->decimal('allocated_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();

            $table->unique(['subscription_period_id', 'course_id', 'instructor_id'], 'sub_allocations_unique');
        });
    }
    public function down() {
        Schema::dropIfExists('subscription_allocations');
    }

};
