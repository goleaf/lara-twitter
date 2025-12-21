<?php

namespace App\Models;

use DialloIbrahima\SmartCache\HasSmartCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    use HasFactory, HasSmartCache;

    public const REPLY_EVERYONE = 'everyone';
    public const REPLY_FOLLOWING = 'following';
    public const REPLY_MENTIONED = 'mentioned';
    public const REPLY_NONE = 'none';

    protected $attributes = [
        'is_published' => true,
    ];

    protected $fillable = [
        'user_id',
        'reply_to_id',
        'repost_of_id',
        'reply_policy',
        'is_reply_like',
        'body',
        'location',
        'is_published',
        'scheduled_for',
        'video_path',
        'video_mime_type',
    ];

    protected function casts(): array
    {
        return [
            'is_reply_like' => 'boolean',
            'is_published' => 'boolean',
            'scheduled_for' => 'datetime',
            'high_engagement_notified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('published', function (Builder $builder): void {
            $builder->where('is_published', true);
        });
    }

    public static function replyPolicies(): array
    {
        return [
            self::REPLY_EVERYONE,
            self::REPLY_FOLLOWING,
            self::REPLY_MENTIONED,
            self::REPLY_NONE,
        ];
    }

    public function scopeWithViewerContext(Builder $query, ?User $viewer): Builder
    {
        if (! $viewer) {
            return $query;
        }

        return $query->withExists([
            'likes as liked_by_viewer' => fn (Builder $q) => $q->where('user_id', $viewer->id),
            'bookmarks as bookmarked_by_viewer' => fn (Builder $q) => $q->where('user_id', $viewer->id),
            'reposts as reposted_by_viewer' => fn (Builder $q) => $q
                ->where('user_id', $viewer->id)
                ->whereNull('reply_to_id')
                ->where('body', ''),
        ]);
    }

    public function scopeWithPostCardRelations(Builder $query, ?User $viewer = null, bool $withReplies = false): Builder
    {
        if ($viewer) {
            $query->withViewerContext($viewer);
        }

        $postColumns = [
            'posts.id',
            'posts.user_id',
            'posts.body',
            'posts.reply_to_id',
            'posts.repost_of_id',
            'posts.reply_policy',
            'posts.created_at',
            'posts.location',
            'posts.video_path',
            'posts.video_mime_type',
            'posts.is_reply_like',
        ];

        if ($query->getQuery()->columns === null) {
            $query->select($postColumns);
        }

        $counts = $withReplies ? ['likes', 'reposts', 'replies'] : ['likes', 'reposts'];
        $userColumns = ['id', 'name', 'username', 'avatar_path', 'is_verified', 'analytics_enabled', 'is_admin'];
        $imageColumns = ['id', 'post_id', 'path', 'sort_order'];
        $linkPreviewColumns = ['id', 'post_id', 'url', 'site_name', 'title', 'description', 'image_url'];
        $pollColumns = ['id', 'post_id', 'ends_at'];
        $pollOptionColumns = ['id', 'post_poll_id', 'option_text', 'sort_order'];
        $repostColumns = [
            'id',
            'user_id',
            'body',
            'reply_to_id',
            'repost_of_id',
            'reply_policy',
            'created_at',
            'location',
            'video_path',
            'video_mime_type',
            'is_reply_like',
        ];

        return $query->with([
            'user' => fn ($q) => $q->select($userColumns),
            'images' => fn ($q) => $q->select($imageColumns),
            'linkPreview' => fn ($q) => $q->select($linkPreviewColumns),
            'poll' => fn ($q) => $q->select($pollColumns),
            'poll.options' => fn ($q) => $q->select($pollOptionColumns)->withCount('votes'),
            'repostOf' => fn ($q) => $q
                ->select($repostColumns)
                ->when($viewer, fn ($q) => $q->withViewerContext($viewer))
                ->with([
                    'user' => fn ($q) => $q->select($userColumns),
                    'images' => fn ($q) => $q->select($imageColumns),
                    'linkPreview' => fn ($q) => $q->select($linkPreviewColumns),
                    'poll' => fn ($q) => $q->select($pollColumns),
                    'poll.options' => fn ($q) => $q->select($pollOptionColumns)->withCount('votes'),
                ])
                ->withCount($counts),
        ])->withCount($counts);
    }

    public function scopeOrderByFollowBias(Builder $query, User $viewer, string $alias = 'follow_bias'): Builder
    {
        $viewerId = $viewer->id;

        return $query
            ->leftJoin("follows as $alias", function ($join) use ($viewerId, $alias): void {
                $join->on("$alias.followed_id", '=', 'posts.user_id')
                    ->where("$alias.follower_id", '=', $viewerId);
            })
            ->orderByRaw("case when $alias.follower_id is null then 0 else 1 end desc");
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

    public function poll(): HasOne
    {
        return $this->hasOne(PostPoll::class);
    }

    public function linkPreview(): HasOne
    {
        return $this->hasOne(PostLinkPreview::class);
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

    public function toSearchableArray(): array
    {
        $this->loadMissing(['user', 'hashtags']);

        return [
            'id' => $this->id,
            'content' => $this->body,
            'user_name' => $this->user?->name,
            'user_username' => $this->user?->username,
            'hashtags' => $this->hashtags->pluck('tag')->all(),
            'created_at' => $this->created_at?->timestamp,
            'likes_count' => $this->likes_count ?? $this->likes()->count(),
        ];
    }

    public function searchableAs(): string
    {
        return 'posts_index';
    }
}
