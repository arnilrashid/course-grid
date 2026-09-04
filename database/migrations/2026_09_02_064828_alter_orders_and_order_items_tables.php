<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('user_id');
            $table->decimal('discount_total', 10, 2)->default(0)->after('subtotal');
            $table->decimal('tax_total', 10, 2)->default(0)->after('discount_total');
            $table->string('currency', 3)->default('USD')->after('tax_total');
            $table->string('idempotency_key')->nullable()->after('payment_method');
            $table->unique(['user_id', 'idempotency_key']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->default(0)->after('price');
            $table->decimal('tax', 10, 2)->default(0)->after('discount');
            $table->decimal('total', 10, 2)->default(0)->after('tax');
            $table->string('currency', 3)->default('USD')->after('total');
            $table->foreignId('course_bundle_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
        });
    }
};
