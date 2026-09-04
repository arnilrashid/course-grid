<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('earnings', function (Blueprint $table) {
            $table->dropForeign(['order_item_id']);
            if (Schema::hasColumn('earnings', 'payout_id')) {
                $table->dropForeign(['payout_id']);
            }
        });
        Schema::table('earnings', function (Blueprint $table) {
            // order_item_id is kept but we must make it nullable, so drop and recreate it
            $table->dropColumn(['order_item_id', 'gross_amount', 'commission_amount', 'net_amount']); 
            
            $table->foreignId('order_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('refund_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('subscription_allocation_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('reverses_earning_id')->nullable()->constrained('earnings')->restrictOnDelete();
            $table->string('currency', 3)->default('USD');
            $table->decimal('allocation_base_amount', 10, 2);
            $table->decimal('platform_amount', 10, 2);
            $table->decimal('payee_amount', 10, 2);
            $table->decimal('revenue_share_percentage_snapshot', 5, 2);
            $table->string('revenue_channel')->default('course_purchase'); // course_purchase | bundle_purchase | subscription | refund_adjustment
        });
    }
};
