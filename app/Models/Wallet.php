<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'currency_code',
        'status',
        'min_balance_allowed',
        'max_balance_allowed',
        'last_transaction_at',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'min_balance_allowed' => 'decimal:2',
        'max_balance_allowed' => 'decimal:2',
        'last_transaction_at' => 'datetime',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
