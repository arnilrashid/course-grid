<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Assuming there is a coupon_user table with a unique constraint
        if (Schema::hasTable('coupon_user')) {
            Schema::table('coupon_user', function (Blueprint $table) {
                $table->dropUnique(['coupon_id', 'user_id']);
            });
        }
    }
};
