<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SocialAuthService
{
    /**
     * Handle the social login/registration logic.
     * 
     * @param string $provider
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @return User|string Either the resolved User model or an error string
     */
    public function handleProviderCallback(string $provider, $socialUser)
    {
        $identity = UserIdentity::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        // Case 1: Identity exists and is linked to a user
        if ($identity) {
            $user = $identity->user;

            if (!$user || $user->isSuspended()) {
                return 'Account suspended.';
            }

            return $user;
        }

        // Case 2: Identity does not exist, let's check by email
        $userByEmail = User::where('email', $socialUser->getEmail())->first();

        if ($userByEmail) {
            if ($userByEmail->isSuspended()) {
                return 'Account suspended.';
            }

            // If the user's email is not verified, require them to log in with password first to prove ownership
            // or we could auto-verify since Google verified it. The spec says:
            // "Existing user with matching unverified email -> Handle explicitly... Do not create account-takeover vulnerability"
            if (!$userByEmail->hasVerifiedEmail()) {
                return 'An account with this email exists but is unverified. Please log in with your password to link your account securely.';
            }

            // Email is verified, safely link the identity
            $userByEmail->identities()->create([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);

            return $userByEmail;
        }

        // Case 3: Completely new user
        return DB::transaction(function () use ($provider, $socialUser) {
            $newUser = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'email_verified_at' => now(), // Trusted provider
                'password' => null, // No password for OAuth-only users
                'avatar' => $socialUser->getAvatar(),
            ]);

            // Assign default student role
            $newUser->assignRole('student');

            $newUser->identities()->create([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);

            return $newUser;
        });
    }

    /**
     * Link an OAuth identity to the currently authenticated user.
     * 
     * @param User $user
     * @param string $provider
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @return bool|string True on success, error message on failure
     */
    public function linkIdentity(User $user, string $provider, $socialUser)
    {
        $existingIdentity = UserIdentity::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingIdentity) {
            if ($existingIdentity->user_id === $user->id) {
                return 'Account is already linked.';
            }
            return 'This Google account is already linked to another user.';
        }

        $user->identities()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
        ]);

        return true;
    }

    /**
     * Unlink an OAuth identity from a user.
     * Prevents removing if it's their only login method (no password and no other identities).
     */
    public function unlinkIdentity(User $user, string $provider): string|bool
    {
        $identity = $user->identities()->where('provider', $provider)->first();

        if (!$identity) {
            return 'Identity not found.';
        }

        $otherIdentitiesCount = $user->identities()->where('provider', '!=', $provider)->count();

        // If user has no password and no other identities, they would be locked out
        if (empty($user->password) && $otherIdentitiesCount === 0) {
            return 'You cannot remove your only authentication method. Please set a password first.';
        }

        $identity->delete();

        return true;
    }
}
