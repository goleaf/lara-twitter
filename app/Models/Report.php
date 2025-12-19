<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    public const REASON_FAKE_ACCOUNT = 'fake_account';

    public const REASON_HARASSMENT = 'harassment';

    public const REASON_HATE = 'hate';

    public const REASON_VIOLENCE = 'violence';

    public const REASON_SELF_HARM = 'self_harm';

    public const REASON_CHILD_SAFETY = 'child_safety';

    public const REASON_PRIVACY = 'privacy';

    public const REASON_MISINFORMATION = 'misinformation';

    public const REASON_MANIPULATED_MEDIA = 'manipulated_media';

    public const REASON_CIVIC_INTEGRITY = 'civic_integrity';

    public const REASON_COVID_MISINFORMATION = 'covid_misinformation';

    public const REASON_NUDITY = 'nudity';

    public const REASON_SENSITIVE_MEDIA = 'sensitive_media';

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

    protected static function booted(): void
    {
        static::saving(function (self $report): void {
            if (! $report->isDirty('status')) {
                return;
            }

            $isResolved = in_array($report->status, [self::STATUS_RESOLVED, self::STATUS_DISMISSED], true);

            if ($isResolved) {
                $report->resolved_at ??= now();
                $report->resolved_by ??= auth()->id();

                return;
            }

            $report->resolved_at = null;
            $report->resolved_by = null;
        });
    }

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    protected function caseNumber(): Attribute
    {
        return Attribute::get(fn (): string => sprintf('R-%08d', $this->id));
    }

    public static function reasons(): array
    {
        return array_keys(self::reasonLabels());
    }

    public static function reasonsRequiringDetails(): array
    {
        return [
            self::REASON_VIOLENCE,
            self::REASON_SELF_HARM,
            self::REASON_CHILD_SAFETY,
        ];
    }

    public static function reasonLabels(): array
    {
        return [
            self::REASON_SPAM => 'Spam',
            self::REASON_FAKE_ACCOUNT => 'Fake account',
            self::REASON_HARASSMENT => 'Harassment',
            self::REASON_HATE => 'Hateful conduct',
            self::REASON_VIOLENCE => 'Violence or threat',
            self::REASON_SELF_HARM => 'Suicide or self-harm',
            self::REASON_CHILD_SAFETY => 'Child safety',
            self::REASON_PRIVACY => 'Privacy violation',
            self::REASON_MISINFORMATION => 'Misleading information',
            self::REASON_MANIPULATED_MEDIA => 'Synthetic/manipulated media',
            self::REASON_CIVIC_INTEGRITY => 'Civic integrity',
            self::REASON_COVID_MISINFORMATION => 'COVID misinformation',
            self::REASON_NUDITY => 'Nudity or sexual content',
            self::REASON_SENSITIVE_MEDIA => 'Sensitive media not marked',
            self::REASON_ILLEGAL => 'Illegal content',
            self::REASON_COPYRIGHT => 'Intellectual property violation',
            self::REASON_OTHER => 'Other',
        ];
    }

    public static function reasonLabel(string $reason): string
    {
        return self::reasonLabels()[$reason] ?? $reason;
    }

    public static function reasonOptions(): array
    {
        return [
            'Spam & fake' => [
                self::REASON_SPAM => self::reasonLabel(self::REASON_SPAM),
                self::REASON_FAKE_ACCOUNT => self::reasonLabel(self::REASON_FAKE_ACCOUNT),
            ],
            'Abuse & safety' => [
                self::REASON_HARASSMENT => self::reasonLabel(self::REASON_HARASSMENT),
                self::REASON_HATE => self::reasonLabel(self::REASON_HATE),
                self::REASON_VIOLENCE => self::reasonLabel(self::REASON_VIOLENCE),
                self::REASON_SELF_HARM => self::reasonLabel(self::REASON_SELF_HARM),
                self::REASON_CHILD_SAFETY => self::reasonLabel(self::REASON_CHILD_SAFETY),
                self::REASON_PRIVACY => self::reasonLabel(self::REASON_PRIVACY),
            ],
            'Misleading information' => [
                self::REASON_MISINFORMATION => self::reasonLabel(self::REASON_MISINFORMATION),
                self::REASON_MANIPULATED_MEDIA => self::reasonLabel(self::REASON_MANIPULATED_MEDIA),
                self::REASON_CIVIC_INTEGRITY => self::reasonLabel(self::REASON_CIVIC_INTEGRITY),
                self::REASON_COVID_MISINFORMATION => self::reasonLabel(self::REASON_COVID_MISINFORMATION),
            ],
            'Sensitive content' => [
                self::REASON_NUDITY => self::reasonLabel(self::REASON_NUDITY),
                self::REASON_SENSITIVE_MEDIA => self::reasonLabel(self::REASON_SENSITIVE_MEDIA),
            ],
            'Illegal & IP' => [
                self::REASON_ILLEGAL => self::reasonLabel(self::REASON_ILLEGAL),
                self::REASON_COPYRIGHT => self::reasonLabel(self::REASON_COPYRIGHT),
            ],
            'Other' => [
                self::REASON_OTHER => self::reasonLabel(self::REASON_OTHER),
            ],
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
