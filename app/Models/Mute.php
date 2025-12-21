<?php

namespace App\Models;

use DialloIbrahima\SmartCache\HasSmartCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mute extends Model
{
    use HasFactory, HasSmartCache;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'muter_id',
        'muted_id',
    ];

    protected static function booted(): void
    {
        $flush = static function (self $mute): void {
            if (auth()->check() && auth()->id() === $mute->muter_id) {
                auth()->user()->flushCachedRelations();
            }
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function muter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muter_id');
    }

    public function muted(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muted_id');
    }
}
