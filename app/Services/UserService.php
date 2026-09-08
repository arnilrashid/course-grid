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
        $original = $user->getOriginal();

        if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $data['avatar']->store('avatars', 'public');
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        
        $changes = $user->getChanges();
        $oldValues = array_intersect_key($original, $changes);
        $newValues = $changes;
        
        unset($oldValues['updated_at']);
        unset($newValues['updated_at']);
        
        if (!empty($newValues)) {
            \App\Services\Admin\AuditLogger::log('update_profile', $user, $oldValues, $newValues);
        }
    }

    /**
     * Delete the user's account.
     */
    public function deleteAccount(User $user): void
    {
        $user->delete();
    }
}
