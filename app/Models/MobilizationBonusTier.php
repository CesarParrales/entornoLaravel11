<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilizationBonusTier extends Model
{
    use HasFactory;

    protected $table = 'mobilization_bonus_tiers';

    protected $fillable = [
        'rank_id',
        'required_consecutive_periods',
        'bonus_amount',
        'is_active',
    ];

    protected $casts = [
        'required_consecutive_periods' => 'integer',
        'bonus_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }
}
