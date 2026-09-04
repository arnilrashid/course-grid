<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_attempt_id')->constrained()->restrictOnDelete();
            $table->string('provider');
            $table->string('provider_transaction_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status');
            $table->timestamps();

            $table->unique(['provider', 'provider_transaction_id']);
        });
    }
    public function down() {
        Schema::dropIfExists('payment_transactions');
    }

};
