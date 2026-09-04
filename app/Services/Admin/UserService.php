<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * Get paginated users with advanced search.
     */
    public function getUsers(string $search = null, string $role = null): LengthAwarePaginator
    {
        $query = User::with('roles')->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->role($role);
        }

        return $query->paginate(20);
    }

    /**
     * Get user activity from AuditLog
     */
    public function getUserActivity(User $user): LengthAwarePaginator
    {
        return AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    /**
     * Get user active sessions
     */
    public function getUserSessions(User $user): \Illuminate\Support\Collection
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            });
    }

    /**
     * Update user profile settings
     */
    public function updateProfile(User $user, array $data): void
    {
        $oldValues = $user->only(['name', 'email']);
        
        $user->name = $data['name'];
        $user->email = $data['email'];
        
        if (!empty($data['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old avatar if exists
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $data['avatar']->store('avatars', 'public');
        }
        
        $user->save();
        
        $newValues = $user->only(['name', 'email']);
        if (!empty($data['password'])) {
            $newValues['password'] = '********'; // Mask password in audit log
            $oldValues['password'] = '********';
        }
        
        AuditLogger::log('update_profile', $user, $oldValues, $newValues);
    }

    /**
     * Update user role
     */
    public function updateRole(User $user, ?string $roleName): void
    {
        if ($user->id === auth()->id() && $user->hasRole('admin') && $roleName !== 'admin') {
            throw new \Exception("You cannot remove your own admin role.");
        }

        // We only allow one primary role for simplicity in the UI, syncRoles removes previous ones
        if ($roleName) {
            $user->syncRoles([$roleName]);
        } else {
            // Unassign all roles
            $user->syncRoles([]);
        }

        AuditLogger::log('update_role', $user, [], ['new_role' => $roleName]);
    }

    /**
     * Suspend a user.
     */
    public function suspend(User $user, string $reason = null): void
    {
        if ($user->id === auth()->id()) {
            throw new \Exception("You cannot suspend yourself.");
        }

        if ($user->hasRole(['super-admin', 'admin'])) {
            throw new \Exception("Admin accounts cannot be suspended.");
        }

        $oldValues = ['suspended_at' => $user->suspended_at];
        
        $user->suspend();
        
        AuditLogger::log('suspend', $user, $oldValues, ['suspended_at' => $user->fresh()->suspended_at, 'reason' => $reason]);
    }

    /**
     * Unsuspend a user.
     */
    public function unsuspend(User $user, string $reason = null): void
    {
        $oldValues = ['suspended_at' => $user->suspended_at];
        
        $user->unsuspend();
        
        AuditLogger::log('unsuspend', $user, $oldValues, ['suspended_at' => null, 'reason' => $reason]);
    }

    /**
     * Revoke active sessions for a user
     */
    public function revokeSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
        
        // Also invalidate remember token to fully log out users with "Remember Me" checked
        $user->forceFill(['remember_token' => null])->save();
        
        // Invalidate API tokens
        $user->tokens()->delete();
        
        AuditLogger::log('revoke_sessions', $user, [], []);
    }

    /**
     * Revoke a single session for a user
     */
    public function revokeSession(User $user, string $sessionId): void
    {
        DB::table('sessions')->where('id', $sessionId)->where('user_id', $user->id)->delete();
        
        // If we revoke a session, we should also clear the remember token, otherwise
        // the user's device will just use the remember_web cookie to instantly create a new session
        $user->forceFill(['remember_token' => null])->save();
        
        AuditLogger::log('revoke_single_session', $user, [], ['session_id' => $sessionId]);
    }
    /**
     * Delete a user (Soft Delete + Anonymize + Transfer Courses)
     */
    public function deleteUser(User $user, ?User $transferTo = null): void
    {
        if ($user->id === auth()->id()) {
            throw new \Exception("You cannot delete yourself.");
        }

        if ($user->hasRole(['super-admin', 'admin'])) {
            throw new \Exception("Admin accounts cannot be deleted.");
        }

        DB::transaction(function () use ($user, $transferTo) {
            // Reassign courses to specified user, OR default to Super Admin (ID 1)
            $newOwnerId = $transferTo ? $transferTo->id : 1;
            
            // Transfer ownership of courses
            DB::table('courses')
                ->where('user_id', $user->id)
                ->update(['user_id' => $newOwnerId]);

            // Invalidate active sessions
            DB::table('sessions')->where('user_id', $user->id)->delete();
            
            // Invalidate API tokens
            $user->tokens()->delete();

            // Clear remember token
            $user->forceFill(['remember_token' => null])->save();

            // Remove roles to clean up authorization
            $user->syncRoles([]);

            // Anonymize user details to comply with privacy laws
            // We keep the ID for foreign keys (orders, enrollments)
            $timestamp = now()->timestamp;
            $oldValues = $user->only(['name', 'email', 'avatar']);
            
            $user->forceFill([
                'name' => 'Deleted User',
                'email' => "deleted_{$user->id}_{$timestamp}@coursegrid.local",
                'avatar' => null,
            ])->save();
            
            // Delete old avatar file if it existed
            if ($oldValues['avatar'] ?? false) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldValues['avatar']);
            }

            // Finally, soft delete the user
            $user->delete();

            AuditLogger::log('delete_user', $user, $oldValues, ['transferred_courses_to' => $newOwnerId]);
        });
    }
}
