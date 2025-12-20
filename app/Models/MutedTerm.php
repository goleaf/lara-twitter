<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutedTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'term',
        'whole_word',
        'only_non_followed',
        'mute_timeline',
        'mute_notifications',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'whole_word' => 'boolean',
            'only_non_followed' => 'boolean',
            'mute_timeline' => 'boolean',
            'mute_notifications' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        $flush = static function (self $term): void {
            if (auth()->check() && auth()->id() === $term->user_id) {
                auth()->user()->flushCachedRelations();
            }
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
