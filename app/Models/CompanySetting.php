<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc',
        'legal_name',
        'commercial_name',
        'logo_platform_light_path',
        'logo_platform_dark_path',
        'logo_invoicing_path',
        'favicon_path',
        'country_id',
        'province_id',
        'city_id',
        'address',
        'is_special_taxpayer',
        'invoice_sequence_start',
        'vat_rate',
        'invoice_establishment_code',
        'invoice_emission_point_code',
        'phone_fixed',
        'phone_mobile',
        'email_primary',
        'email_secondary',
        'social_media_links',
    ];

    protected $casts = [
        'is_special_taxpayer' => 'boolean',
        'invoice_sequence_start' => 'integer',
        'vat_rate' => 'decimal:2',
        'social_media_links' => 'array',
        'country_id' => 'integer',
        'province_id' => 'integer',
        'city_id' => 'integer',
    ];

    /**
     * Get the country associated with the company settings.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the province associated with the company settings.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the city associated with the company settings.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the bank accounts for the company.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(CompanyBankAccount::class);
    }
}
