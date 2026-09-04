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
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->json('learning_objectives')->nullable();
            $table->json('requirements')->nullable();
            $table->json('audience')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);

            $table->dropColumn([
                'meta_title',
                'meta_description',
                'learning_objectives',
                'requirements',
                'audience',
                'rejection_reason',
                'reviewed_by',
                'reviewed_at',
            ]);
        });
    }
};
