<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEarnedAward extends Model
{
    use HasFactory;

    protected $table = 'user_earned_awards';

    protected $fillable = [
        'user_id',
        'bonus_type_id',
        'award_description',
        'awarded_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bonusType(): BelongsTo
    {
        return $this->belongsTo(BonusType::class);
    }
}
