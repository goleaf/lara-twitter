<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'moment_id',
        'post_id',
        'sort_order',
    ];

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}

