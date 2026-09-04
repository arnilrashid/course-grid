<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('provider');
            $table->string('provider_payment_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('idempotency_key');
            $table->string('status')->default('pending');
            $table->text('error_information')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_payment_id']);
        });
    }
    public function down() {
        Schema::dropIfExists('payment_attempts');
    }

};
