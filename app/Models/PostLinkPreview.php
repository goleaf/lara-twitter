<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostLinkPreview extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'url',
        'site_name',
        'title',
        'description',
        'image_url',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'fetched_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}

