<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar_path',
        'header_path',
        'bio',
        'location',
        'website',
        'birth_date',
        'birth_date_visibility',
        'is_admin',
        'is_premium',
        'is_verified',
        'dm_policy',
        'dm_allow_requests',
        'timeline_settings',
        'pinned_post_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_premium' => 'boolean',
            'is_verified' => 'boolean',
            'dm_allow_requests' => 'boolean',
            'timeline_settings' => 'array',
            'notification_settings' => 'array',
            'interest_hashtags' => 'array',
            'analytics_enabled' => 'boolean',
        ];
    }

    public function timelineSetting(string $key, bool $default): bool
    {
        $settings = $this->timeline_settings ?? [];

        return (bool) ($settings[$key] ?? $default);
    }

    public const DM_EVERYONE = 'everyone';

    public const DM_FOLLOWING = 'following';

    public const DM_NONE = 'none';

    public static function dmPolicies(): array
    {
        return [
            self::DM_EVERYONE,
            self::DM_FOLLOWING,
            self::DM_NONE,
        ];
    }

    public const BIRTH_DATE_PUBLIC = 'public';

    public const BIRTH_DATE_FOLLOWERS = 'followers';

    public const BIRTH_DATE_PRIVATE = 'private';

    public static function birthDateVisibilities(): array
    {
        return [
            self::BIRTH_DATE_PUBLIC,
            self::BIRTH_DATE_FOLLOWERS,
            self::BIRTH_DATE_PRIVATE,
        ];
    }

    public function canShowBirthDateTo(?User $viewer): bool
    {
        if (! $this->birth_date) {
            return false;
        }

        $visibility = $this->birth_date_visibility ?: self::BIRTH_DATE_PUBLIC;

        if ($visibility === self::BIRTH_DATE_PUBLIC) {
            return true;
        }

        if (! $viewer) {
            return false;
        }

        if ($viewer->is($this)) {
            return true;
        }

        if ($visibility === self::BIRTH_DATE_PRIVATE) {
            return false;
        }

        return $viewer
            ->following()
            ->where('followed_id', $this->id)
            ->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        return (bool) $this->is_admin;
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'likes');
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')->withTimestamps();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }

    public function getHeaderUrlAttribute(): ?string
    {
        if (! $this->header_path) {
            return null;
        }

        return Storage::disk('public')->url($this->header_path);
    }

    public function wantsNotification(string $type): bool
    {
        $settings = $this->notification_settings ?? [];

        $defaults = [
            'likes' => true,
            'reposts' => true,
            'replies' => true,
            'mentions' => true,
            'follows' => true,
            'dms' => true,
            'lists' => true,
            'followed_posts' => false,
        ];

        return (bool) ($settings[$type] ?? $defaults[$type] ?? true);
    }

    public function allowsNotificationFrom(User $actor): bool
    {
        if ($this->id === $actor->id) {
            return false;
        }

        if ($this->isBlockedEitherWay($actor)) {
            return false;
        }

        if ($this->hasMuted($actor)) {
            return false;
        }

        $settings = $this->notification_settings ?? [];

        if ((bool) ($settings['only_verified'] ?? false) && ! $actor->is_verified) {
            return false;
        }

        if ((bool) ($settings['only_following'] ?? false)) {
            $isFollowingActor = $this->following()->where('followed_id', $actor->id)->exists();
            if (! $isFollowingActor) {
                return false;
            }
        }

        if ((bool) ($settings['quality_filter'] ?? false)) {
            $hasAvatar = (bool) $actor->avatar_path;
            $hasVerifiedEmail = (bool) $actor->email_verified_at;

            if (! $hasAvatar || ! $hasVerifiedEmail) {
                return false;
            }
        }

        return true;
    }

    public function conversationParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function conversations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Conversation::class,
            ConversationParticipant::class,
            'user_id',
            'id',
            'id',
            'conversation_id',
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'bookmarks')->withTimestamps();
    }

    public function listsOwned(): HasMany
    {
        return $this->hasMany(UserList::class, 'owner_id');
    }

    public function listsMemberOf(): BelongsToMany
    {
        return $this->belongsToMany(UserList::class, 'user_list_user')->withTimestamps();
    }

    public function listsSubscribed(): BelongsToMany
    {
        return $this->belongsToMany(UserList::class, 'user_list_subscriptions')->withTimestamps();
    }

    public function blocksInitiated(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function blocksReceived(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }

    public function mutesInitiated(): HasMany
    {
        return $this->hasMany(Mute::class, 'muter_id');
    }

    public function mutedTerms(): HasMany
    {
        return $this->hasMany(MutedTerm::class);
    }

    public function hasBlocked(User $other): bool
    {
        return $this->blocksInitiated()->where('blocked_id', $other->id)->exists();
    }

    public function isBlockedBy(User $other): bool
    {
        return $this->blocksReceived()->where('blocker_id', $other->id)->exists();
    }

    public function isBlockedEitherWay(User $other): bool
    {
        return $this->hasBlocked($other) || $this->isBlockedBy($other);
    }

    public function hasMuted(User $other): bool
    {
        return $this->mutesInitiated()->where('muted_id', $other->id)->exists();
    }

    public function excludedUserIds(): Collection
    {
        $mutedIds = $this->mutesInitiated()->pluck('muted_id');
        $blockedIds = $this->blocksInitiated()->pluck('blocked_id');
        $blockedByIds = $this->blocksReceived()->pluck('blocker_id');

        return $mutedIds->merge($blockedIds)->merge($blockedByIds)->unique()->values();
    }

    public function spacesHosted(): HasMany
    {
        return $this->hasMany(Space::class, 'host_user_id');
    }

    public function spaceParticipants(): HasMany
    {
        return $this->hasMany(SpaceParticipant::class);
    }

    public function moments(): HasMany
    {
        return $this->hasMany(Moment::class, 'owner_id');
    }

    public function pinnedPost(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Post::class, 'pinned_post_id');
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
