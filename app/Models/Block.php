<?php

namespace App\Models;

use DialloIbrahima\SmartCache\HasSmartCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    use HasFactory, HasSmartCache;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

    protected static function booted(): void
    {
        $flush = static function (self $block): void {
            if (! auth()->check()) {
                return;
            }

            $userId = auth()->id();
            if ($userId === $block->blocker_id || $userId === $block->blocked_id) {
                auth()->user()->flushCachedRelations();
            }
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }
}
