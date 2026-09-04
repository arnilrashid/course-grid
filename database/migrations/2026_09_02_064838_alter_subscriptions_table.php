<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
        });
    }
};
