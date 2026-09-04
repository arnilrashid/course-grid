<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    /**
     * Update the user's profile information.
     */
    public function updateProfile(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }

    /**
     * Delete the user's account.
     */
    public function deleteAccount(User $user): void
    {
        $user->delete();
    }
}
