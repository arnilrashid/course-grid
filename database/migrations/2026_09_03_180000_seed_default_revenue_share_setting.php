<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Seed the default revenue share percentage if not already present
        $exists = DB::table('settings')
            ->where('key', 'default_revenue_share_percentage')
            ->exists();

        if (! $exists) {
            DB::table('settings')->insert([
                'key' => 'default_revenue_share_percentage',
                'value' => '70.00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'default_revenue_share_percentage')
            ->delete();
    }
};
