<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranBookmark extends Model
{
    protected $fillable = [
        'user_id',
        'surah_number',
        'ayah_number',
        'surah_name',
        'ayah_text',
    ];

    /**
     * The user who owns this bookmark.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
