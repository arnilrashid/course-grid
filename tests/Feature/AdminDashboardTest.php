<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\RoleSeeder;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Seed roles so 'admin' role exists
        $this->seed(RoleSeeder::class);
    }

    private function getAdminUser()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    public function test_guests_and_normal_users_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get('/admin');
        $response->assertForbidden();
    }

    public function test_admin_can_access_dashboard()
    {
        $admin = $this->getAdminUser();
        $this->actingAs($admin);
        
        $response = $this->get('/admin');
        $response->assertOk();
    }

    public function test_suspend_user_writes_audit_log()
    {
        $admin = $this->getAdminUser();
        $this->actingAs($admin);
        
        $targetUser = User::factory()->create();

        $response = $this->post("/admin/users/{$targetUser->id}/suspend", [
            'reason' => 'Violation of terms',
        ]);
        
        $response->assertRedirect();
        
        $targetUser->refresh();
        $this->assertNotNull($targetUser->suspended_at);
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'suspend',
            'auditable_type' => User::class,
            'auditable_id' => $targetUser->id,
        ]);
        
        $log = AuditLog::where('action', 'suspend')->first();
        $this->assertEquals('Violation of terms', $log->new_values['reason'] ?? null);
    }

    public function test_approve_course_writes_audit_log()
    {
        $admin = $this->getAdminUser();
        $this->actingAs($admin);
        
        $course = Course::create([
            'user_id' => $admin->id,
            'title' => 'Test',
            'slug' => 'test-123',
            'status' => \App\Enums\CourseStatus::IN_REVIEW,
            'price' => 10,
        ]);

        $response = $this->post("/admin/courses/{$course->id}/approve");
        $response->assertRedirect();
        
        $course->refresh();
        $this->assertEquals(\App\Enums\CourseStatus::PUBLISHED, $course->status);
        
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'approve_course',
            'auditable_type' => Course::class,
            'auditable_id' => $course->id,
        ]);
    }
}
