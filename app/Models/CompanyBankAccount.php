<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_setting_id',
        'bank_id',
        'account_type',
        'account_number',
        'account_holder_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bank_id' => 'integer',
        'company_setting_id' => 'integer',
    ];

    /**
     * Get the company settings this bank account belongs to.
     */
    public function companySetting(): BelongsTo
    {
        return $this->belongsTo(CompanySetting::class);
    }

    /**
     * Get the bank associated with this account.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
