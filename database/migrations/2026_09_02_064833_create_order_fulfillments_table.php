<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('order_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_transaction_id')->constrained()->restrictOnDelete();
            $table->string('status');
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();

            $table->unique('order_id');
        });
    }
    public function down() {
        Schema::dropIfExists('order_fulfillments');
    }

};
