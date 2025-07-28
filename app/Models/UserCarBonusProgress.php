<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCarBonusProgress extends Model
{
    use HasFactory;

    protected $table = 'user_car_bonus_progress';

    protected $fillable = [
        'user_id',
        'bonus_type_id',
        'current_cycle_number',
        'payments_made_this_cycle',
        'is_eligible_for_cycle',
        'cycle_config_snapshot',
        // 'cycle_started_at',
        // 'last_payment_at',
    ];

    protected $casts = [
        'current_cycle_number' => 'integer',
        'payments_made_this_cycle' => 'integer',
        'is_eligible_for_cycle' => 'boolean',
        'cycle_config_snapshot' => 'array',
        // 'cycle_started_at' => 'datetime',
        // 'last_payment_at' => 'datetime',
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
