<?php

namespace App\Models;

use DialloIbrahima\SmartCache\HasSmartCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttachment extends Model
{
    use HasFactory, HasSmartCache;

    protected $fillable = [
        'message_id',
        'path',
        'mime_type',
        'sort_order',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}

