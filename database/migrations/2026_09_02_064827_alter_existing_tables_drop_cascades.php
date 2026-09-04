<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->foreign('course_id')->references('id')->on('courses')->restrictOnDelete();
        });
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete();
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
        Schema::table('earnings', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->foreign('instructor_id')->references('id')->on('users')->restrictOnDelete();
        });
    }
    public function down() {
        // Reverse operations
    }
};
