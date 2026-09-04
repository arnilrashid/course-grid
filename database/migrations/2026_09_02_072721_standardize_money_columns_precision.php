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
            $table->decimal('price', 10, 2)->default(0)->change();
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('value', 10, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('course_bundles', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_bundles', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 8, 2)->change();
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('value', 8, 2)->change();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(0)->change();
        });
    }
};
