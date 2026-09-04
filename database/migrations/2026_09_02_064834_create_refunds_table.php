<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->constrained()->restrictOnDelete();
            $table->string('provider');
            $table->string('provider_refund_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status');
            $table->timestamps();

            $table->unique(['provider', 'provider_refund_id']);
        });
    }
    public function down() {
        Schema::dropIfExists('refunds');
    }

};
