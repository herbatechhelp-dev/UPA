<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'date',
        'topic',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    /**
     * Get the mentoring group this activity belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get attendances for this activity.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
