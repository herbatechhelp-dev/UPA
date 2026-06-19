<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ustad_id',
        'leader_id',
        'is_delegated',
        'delegated_until',
    ];

    protected function casts(): array
    {
        return [
            'is_delegated' => 'boolean',
            'delegated_until' => 'datetime',
        ];
    }

    /**
     * Get the Ustad (Pembina) of this group.
     */
    public function ustad(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustad_id');
    }

    /**
     * Get the Ketua Kelompok of this group.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get members of this group.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    /**
     * Get activities/sessions of this group.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Checks if attendance approval delegation is active.
     */
    public function isDelegationActive(): bool
    {
        if (!$this->is_delegated) {
            return false;
        }

        if ($this->delegated_until === null) {
            // Indefinite delegation until revoked
            return true;
        }

        return Carbon::now()->lessThanOrEqualTo($this->delegated_until);
    }
}
