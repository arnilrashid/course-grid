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
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->nullable()->after('price');
            $table->unsignedInteger('reviews_count')->default(0)->after('average_rating');
            $table->unsignedInteger('enrollments_count')->default(0)->after('reviews_count');
            
            // Add composite index for status and category_id (status is leading column)
            $table->index(['status', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['status', 'category_id']);
            $table->dropColumn(['average_rating', 'reviews_count', 'enrollments_count']);
        });
    }
};
