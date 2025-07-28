<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPeriodRank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_id',
        'rank_id',
        'achieved_at',
        'calculation_details',
    ];

    protected $casts = [
        'achieved_at' => 'datetime',
        'calculation_details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }
}
