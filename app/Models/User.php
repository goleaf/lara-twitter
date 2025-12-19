<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
        'is_admin',
        'is_premium',
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
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_premium' => 'boolean',
            'notification_settings' => 'array',
            'interest_hashtags' => 'array',
            'analytics_enabled' => 'boolean',
        ];
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
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id');
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
        ];

        return (bool) ($settings[$type] ?? $defaults[$type] ?? true);
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

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
