<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('order_item_bundle_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->decimal('catalog_price_snapshot', 10, 2);
            $table->decimal('allocation_percentage', 5, 2);
            $table->decimal('allocated_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('order_item_bundle_allocations');
    }

};
