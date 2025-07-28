<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialFreedomCommissionTier extends Model
{
    use HasFactory;

    protected $table = 'financial_freedom_commission_tiers';

    protected $fillable = [
        'rank_id',
        'max_points_for_rank',
        'percentage',
        'is_active',
    ];

    protected $casts = [
        'max_points_for_rank' => 'integer',
        'percentage' => 'decimal:4', // Ej. 0.1400 para 14.00%
        'is_active' => 'boolean',
    ];

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }
}
