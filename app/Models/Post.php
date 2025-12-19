<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    public const REPLY_EVERYONE = 'everyone';
    public const REPLY_FOLLOWING = 'following';
    public const REPLY_MENTIONED = 'mentioned';
    public const REPLY_NONE = 'none';

    protected $fillable = [
        'user_id',
        'reply_to_id',
        'repost_of_id',
        'reply_policy',
        'body',
    ];

    public static function replyPolicies(): array
    {
        return [
            self::REPLY_EVERYONE,
            self::REPLY_FOLLOWING,
            self::REPLY_MENTIONED,
            self::REPLY_NONE,
        ];
    }

    public function canBeRepliedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $this->loadMissing('user');

        if ($user->id === $this->user_id) {
            return true;
        }

        if ($user->isBlockedEitherWay($this->user)) {
            return false;
        }

        $policy = $this->reply_policy ?: self::REPLY_EVERYONE;

        if ($policy === self::REPLY_EVERYONE) {
            return true;
        }

        if ($policy === self::REPLY_NONE) {
            return false;
        }

        if ($policy === self::REPLY_FOLLOWING) {
            return $this->user
                ->following()
                ->where('followed_id', $user->id)
                ->exists();
        }

        if ($policy === self::REPLY_MENTIONED) {
            return $this->mentions()
                ->where('mentioned_user_id', $user->id)
                ->exists();
        }

        return true;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function repostOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'repost_of_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'reply_to_id');
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(self::class, 'repost_of_id');
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('sort_order');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
