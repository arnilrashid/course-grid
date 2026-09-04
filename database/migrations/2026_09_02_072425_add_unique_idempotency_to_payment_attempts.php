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
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->unique(['order_id', 'idempotency_key'], 'payment_attempts_idempotency_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            // First recreate the standard foreign key index if MySQL dropped it
            $table->index('order_id');
        });
        
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->dropUnique('payment_attempts_idempotency_unique');
        });
    }
};
