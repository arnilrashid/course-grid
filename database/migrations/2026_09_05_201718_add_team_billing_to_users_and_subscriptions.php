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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('team_owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('seats')->default(1)->after('plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_owner_id']);
            $table->dropColumn('team_owner_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('seats');
        });
    }
};
