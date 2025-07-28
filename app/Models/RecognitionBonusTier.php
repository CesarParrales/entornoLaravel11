<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecognitionBonusTier extends Model
{
    use HasFactory;

    protected $table = 'recognition_bonus_tiers';

    protected $fillable = [
        'rank_id',
        'annual_periods_required',
        'bonus_amount',
        'is_active',
    ];

    protected $casts = [
        'annual_periods_required' => 'integer',
        'bonus_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }
}
