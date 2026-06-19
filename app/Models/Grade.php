<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ustad_id',
        'month',
        'year',
        'score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'month' => 'integer',
            'year' => 'integer',
        ];
    }

    /**
     * Get the user (member) being graded.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the Ustad who gave this grade.
     */
    public function ustad(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ustad_id');
    }
}
