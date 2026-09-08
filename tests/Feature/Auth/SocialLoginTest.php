<?php

use App\Models\User;
use App\Models\UserIdentity;
use Laravel\Socialite\Facades\Socialite;
use Mockery\MockInterface;
use Laravel\Socialite\Two\User as SocialiteUser;

function mockGoogleUser($email, $id = '123456789')
{
    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')->andReturn($id);
    $abstractUser->shouldReceive('getName')->andReturn('Google User');
    $abstractUser->shouldReceive('getEmail')->andReturn($email);
    $abstractUser->shouldReceive('getAvatar')->andReturn('https://avatar.test');
    $abstractUser->shouldReceive('getNickname')->andReturn(null);

    $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
}

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student']);
});

test('new user can sign up via google', function () {
    mockGoogleUser('new@example.com');

    $response = $this->get('/auth/google/callback');
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();

    $user = User::where('email', 'new@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasVerifiedEmail())->toBeTrue();
    expect($user->identities()->count())->toBe(1);
    expect($user->identities->first()->provider)->toBe('google');
});

test('existing user with verified email is securely linked', function () {
    $user = User::factory()->create(['email' => 'existing@example.com']);
    mockGoogleUser('existing@example.com', 'google_123');

    $response = $this->get('/auth/google/callback');
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);

    expect($user->identities()->count())->toBe(1);
});

test('existing user with unverified email requires password to link', function () {
    $user = User::factory()->unverified()->create(['email' => 'unverified@example.com']);
    mockGoogleUser('unverified@example.com');

    $response = $this->get('/auth/google/callback');
    $response->assertRedirect('/login');
    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('user with 2FA enabled is challenged after google oauth', function () {
    $user = User::factory()->withTwoFactor()->create();
    // They already linked their account previously
    $user->identities()->create(['provider' => 'google', 'provider_id' => '2fa_google_id']);
    
    mockGoogleUser($user->email, '2fa_google_id');

    $response = $this->get('/auth/google/callback');
    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('suspended user cannot login via google', function () {
    $user = User::factory()->create();
    $user->suspend();
    $user->identities()->create(['provider' => 'google', 'provider_id' => 'banned_google_id']);
    
    mockGoogleUser($user->email, 'banned_google_id');

    $response = $this->get('/auth/google/callback');
    $response->assertRedirect('/login');
    $response->assertSessionHasErrors(['email' => 'Account suspended.']);
    $this->assertGuest();
});

test('duplicate google identity belongs to another user', function () {
    // User A has the google identity
    $userA = User::factory()->create();
    $userA->identities()->create(['provider' => 'google', 'provider_id' => 'shared_google_id']);

    // User B tries to link it
    $userB = User::factory()->create();
    $this->actingAs($userB);

    mockGoogleUser('other@example.com', 'shared_google_id');

    $response = $this->get('/auth/google/callback');
    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrors(['social']);
    
    expect($userB->identities()->count())->toBe(0);
});

test('user cannot remove google if it is their only authentication method', function () {
    // User has NO password and NO other identities
    $user = User::factory()->create(['password' => null]);
    $user->identities()->create(['provider' => 'google', 'provider_id' => 'google_only_id']);

    $this->actingAs($user);

    $response = $this->delete('/auth/google/unlink');
    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrors(['social']);
    
    expect($user->identities()->count())->toBe(1);
});

test('user can remove google if they have a password', function () {
    $user = User::factory()->create(); // Has password
    $user->identities()->create(['provider' => 'google', 'provider_id' => 'google_id']);

    $this->actingAs($user);

    $response = $this->delete('/auth/google/unlink');
    $response->assertRedirect(route('profile.edit'));
    
    expect($user->identities()->count())->toBe(0);
});
