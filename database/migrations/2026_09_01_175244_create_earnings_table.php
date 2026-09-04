<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('gross_amount', 8, 2); // what the student paid
            $table->decimal('commission_amount', 8, 2); // platform's cut
            $table->decimal('net_amount', 8, 2); // instructor's actual earning
            $table->foreignId('payout_id')->nullable()->constrained()->nullOnDelete(); // set once bundled into a monthly payout
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
