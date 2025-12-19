<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UserList extends Model
{
    use HasFactory;

    protected $table = 'user_lists';

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_list_user')->withTimestamps();
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_list_subscriptions')->withTimestamps();
    }

    public function isVisibleTo(?User $viewer): bool
    {
        if (! $this->is_private) {
            return true;
        }

        if (! $viewer) {
            return false;
        }

        if ($viewer->id === $this->owner_id) {
            return true;
        }

        return $this->members()->where('user_id', $viewer->id)->exists();
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
