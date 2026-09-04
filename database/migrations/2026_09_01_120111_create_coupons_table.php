<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->cascadeOnDelete(); // null = platform-wide, created by admin
            $table->string('code')->unique(); // e.g. "SAVE20"
            $table->string('discount_type'); // percent | fixed
            $table->decimal('value', 8, 2); // 20 (for 20%) or 10.00 (for $10 off)
            $table->unsignedInteger('usage_limit')->nullable(); // null = unlimited
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
