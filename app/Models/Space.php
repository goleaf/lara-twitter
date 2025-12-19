<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_user_id',
        'title',
        'description',
        'scheduled_for',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SpaceParticipant::class);
    }

    public function isLive(): bool
    {
        return (bool) $this->started_at && ! $this->ended_at;
    }

    public function isEnded(): bool
    {
        return (bool) $this->ended_at;
    }
}

