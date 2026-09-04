<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class UserSuspensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspend_method_updates_db_and_clears_sessions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Simulate a session in the DB
        DB::table('sessions')->insert([
            'id' => 'test-session-id',
            'user_id' => $user->id,
            'payload' => 'payload',
            'last_activity' => time(),
        ]);
        
        // Simulate an API token
        $user->createToken('test-token');

        $this->assertDatabaseHas('sessions', ['user_id' => $user->id]);
        $this->assertCount(1, $user->tokens);

        $user->suspend();

        $this->assertTrue($user->isSuspended());
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_suspended_user_cannot_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        $user->suspend();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'This account has been suspended.']);
        $this->assertGuest();
    }

    public function test_unsuspended_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        $user->suspend();
        $user->unsuspend();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_suspended_user_is_kicked_out_mid_flight()
    {
        $user = User::factory()->create();
        
        // Login normally
        $this->actingAs($user);
        
        // Hitting a protected route should work
        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Suspend the user behind the scenes
        $user->suspend();

        // Hitting the protected route should now kick them out
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        
        $this->assertGuest();
    }
}
