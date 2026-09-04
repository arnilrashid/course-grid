<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Earning;
use App\Services\EarningService;

class EarningIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_earning_creation_is_idempotent()
    {
        $instructor = User::create([
            'name' => 'Instructor',
            'email' => 'inst@example.com',
            'password' => bcrypt('password'),
        ]);

        $service = new EarningService();
        $sourceKey = 'course_purchase:999';

        $data = [
            'source_key' => $sourceKey,
            'instructor_id' => $instructor->id,
            'revenue_channel' => 'course_purchase',
            'currency' => 'USD',
            'allocation_base_amount' => 100.00,
            'platform_amount' => 30.00,
            'payee_amount' => 70.00,
            'revenue_share_percentage_snapshot' => 70.00,
        ];

        // 1. Create first earning
        $earning1 = $service->createEarningIdempotently($data);

        $this->assertDatabaseCount('earnings', 1);

        // 2. Simulate queue job retry: create second earning with same source_key
        $earning2 = $service->createEarningIdempotently($data);

        // Should return the exact same model instance/id
        $this->assertEquals($earning1->id, $earning2->id);
        $this->assertDatabaseCount('earnings', 1);
    }
}
