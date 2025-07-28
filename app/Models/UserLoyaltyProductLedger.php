<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoyaltyProductLedger extends Model
{
    use HasFactory;

    protected $table = 'user_loyalty_product_ledger';

    protected $fillable = [
        'user_id',
        'transaction_type',
        'products_quantity',
        'balance_after_transaction',
        'notes',
        'source_bonus_type_id',
        'source_period_id',
        'source_order_id',
        'processed_at',
    ];

    protected $casts = [
        'products_quantity' => 'integer',
        'balance_after_transaction' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bonusType(): BelongsTo
    {
        return $this->belongsTo(BonusType::class, 'source_bonus_type_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'source_period_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'source_order_id');
    }

    // Método para obtener el saldo actual de un usuario
    public static function getCurrentBalance(int $userId): int
    {
        return static::where('user_id', $userId)->latest('id')->first()->balance_after_transaction ?? 0;
    }
}
