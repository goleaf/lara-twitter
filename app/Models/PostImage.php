<?php

namespace App\Models;

use DialloIbrahima\SmartCache\HasSmartCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImage extends Model
{
    use HasFactory, HasSmartCache;

    protected $fillable = [
        'post_id',
        'path',
        'thumbnail_path',
        'sort_order',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
