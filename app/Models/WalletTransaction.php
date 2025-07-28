<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str; // For UUID

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'transaction_uuid',
        'type',
        'amount',
        'balance_before_transaction',
        'balance_after_transaction',
        'currency_code',
        'description',
        'sourceable_id',
        'sourceable_type',
        'period_id', // Añadido period_id
        'metadata',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before_transaction' => 'decimal:2',
        'balance_after_transaction' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->transaction_uuid)) {
                $model->transaction_uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the wallet that this transaction belongs to.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the user that this transaction is associated with.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source model of the transaction (polymorphic relationship).
     * Example: A BonusPayout, an Order, a WithdrawalRequest, etc.
     */
    public function sourceable()
    {
        return $this->morphTo();
    }

    /**
     * Get the period that this transaction may be associated with.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
}
