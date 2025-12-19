<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mute extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'muter_id',
        'muted_id',
    ];

    public function muter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muter_id');
    }

    public function muted(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muted_id');
    }
}

