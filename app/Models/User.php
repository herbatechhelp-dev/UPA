<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role associated with the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Groups that this user oversees as an Ustad / Pembina.
     */
    public function groupsManaged(): HasMany
    {
        return $this->hasMany(Group::class, 'ustad_id');
    }

    /**
     * Groups that this user leads as a Ketua Kelompok.
     */
    public function groupsLed(): HasMany
    {
        return $this->hasMany(Group::class, 'leader_id');
    }

    /**
     * Mentoring groups that this user is registered in as an ordinary member.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    /**
     * Attendances recorded for this user.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Grades recorded for this user.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'user_id');
    }

    /**
     * Helper to verify user roles.
     */
    public function hasRole(string|array $slugs): bool
    {
        $this->loadMissing('role');
        
        if (!$this->role) {
            return false;
        }

        if (is_array($slugs)) {
            return in_array($this->role->slug, $slugs, true);
        }

        return $this->role->slug === $slugs;
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isUstad(): bool
    {
        return $this->hasRole('ustad');
    }

    public function isLeader(): bool
    {
        return $this->hasRole('leader');
    }

    public function isMember(): bool
    {
        return $this->hasRole('member');
    }
}
