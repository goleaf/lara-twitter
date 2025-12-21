<?php

namespace App\Models;

use DialloIbrahima\SmartCache\HasSmartCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostPollOption extends Model
{
    use HasFactory, HasSmartCache;

    protected $fillable = [
        'post_poll_id',
        'option_text',
        'sort_order',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(PostPoll::class, 'post_poll_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PostPollVote::class);
    }
}

