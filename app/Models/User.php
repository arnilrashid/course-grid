<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'avatar', 'suspended_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
#[Appends(['has_password'])]
class User extends Authenticatable implements PasskeyUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, HasRoles, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function instructorProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Determine if the user has verified their email address.
     * Admins are considered automatically verified to prevent getting blocked from settings.
     */
    public function hasVerifiedEmail(): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    public function identities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserIdentity::class);
    }

    public function conversations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)->withTimestamps();
    }

    /**
     * Suspend the user.
     * Sets suspended_at to now, and deletes all active sessions and tokens.
     */
    public function suspend(): void
    {
        $this->update(['suspended_at' => now()]);
        
        // Also invalidate remember token to fully log out users with "Remember Me" checked
        $this->forceFill(['remember_token' => null])->save();
        
        // Invalidate active sessions
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $this->id)
            ->delete();
            
        // Invalidate API tokens
        $this->tokens()->delete();
    }

    /**
     * Unsuspend the user.
     */
    public function unsuspend(): void
    {
        $this->update(['suspended_at' => null]);
    }

    /**
     * Check if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /**
     * Determine if the user has a password.
     */
    protected function hasPassword(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => !is_null($this->password),
        );
    }

    public function teamOwner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'team_owner_id');
    }

    public function teamMembers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'team_owner_id');
    }

    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * Check if the user has an active subscription, 
     * or if their team owner has an active subscription with available seats.
     */
    public function hasActiveSubscription(): bool
    {
        // Check personal subscription
        if ($this->subscription && $this->subscription->isActive()) {
            return true;
        }

        // Check team owner subscription
        if ($this->team_owner_id && $this->teamOwner && $this->teamOwner->subscription && $this->teamOwner->subscription->isActive()) {
            // Check if the team owner has enough seats
            $usedSeats = $this->teamOwner->teamMembers()->count() + 1; // +1 for the owner
            if ($this->teamOwner->subscription->seats >= $usedSeats) {
                return true;
            }
        }

        return false;
    }
}
