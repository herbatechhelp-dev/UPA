<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'ustad_id',
        'title',
        'content',
        'file_path',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the Ustad who published this material.
     */
    public function ustad(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustad_id');
    }

    /**
     * Scope to only include published materials.
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
              ->where('published_at', '<=', Carbon::now());
    }
}
