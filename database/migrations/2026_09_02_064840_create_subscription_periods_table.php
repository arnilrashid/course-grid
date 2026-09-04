<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('subscription_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->decimal('pool_amount', 10, 2)->default(0);
            $table->boolean('is_allocated')->default(false);
            $table->decimal('pool_percentage_snapshot', 5, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('subscription_periods');
    }

};
