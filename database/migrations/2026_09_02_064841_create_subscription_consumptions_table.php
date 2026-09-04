<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('subscription_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_period_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('consumed_seconds')->default(0);
            $table->timestamps();

            $table->unique(['subscription_period_id', 'course_id', 'user_id'], 'sub_consumptions_unique');
        });
    }
    public function down() {
        Schema::dropIfExists('subscription_consumptions');
    }

};
