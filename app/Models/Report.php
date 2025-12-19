<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const REASON_SPAM = 'spam';
    public const REASON_HARASSMENT = 'harassment';
    public const REASON_HATE = 'hate';
    public const REASON_VIOLENCE = 'violence';
    public const REASON_NUDITY = 'nudity';
    public const REASON_ILLEGAL = 'illegal';
    public const REASON_COPYRIGHT = 'copyright';
    public const REASON_OTHER = 'other';

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'details',
        'status',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public static function reasons(): array
    {
        return [
            self::REASON_SPAM,
            self::REASON_HARASSMENT,
            self::REASON_HATE,
            self::REASON_VIOLENCE,
            self::REASON_NUDITY,
            self::REASON_ILLEGAL,
            self::REASON_COPYRIGHT,
            self::REASON_OTHER,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_REVIEWING,
            self::STATUS_RESOLVED,
            self::STATUS_DISMISSED,
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}

