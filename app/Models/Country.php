<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany for provinces

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code_2',
        'iso_code_3',
        'geoname_id', // Re-added
        'currency_code',
        'currency_symbol',
        'usd_exchange_rate',
        'is_active',
        // New fields
        'phone_country_code',
        'dni_label',
        'dni_format_regex',
        'dni_fixed_length',
        'phone_national_format_regex',
        'phone_national_fixed_length',
        'vat_rate',
        'vat_label',
        'default_language_code',
        'default_timezone',
        // Campos de etiquetas para divisiones administrativas
        'administrative_division_label_1', // ej. Provincia, Estado
        'administrative_division_label_2', // ej. Ciudad, Cantón, Municipio
        'administrative_division_label_3', // ej. Parroquia, Localidad (opcional)
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'usd_exchange_rate' => 'decimal:6',
        'geoname_id' => 'integer', // Re-added
        // New casts
        'dni_fixed_length' => 'integer',
        'phone_national_fixed_length' => 'integer',
        'vat_rate' => 'decimal:4',
    ];

    /**
     * Get the provinces for the country.
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    /**
     * Get the cities for the country (if a direct relationship is needed).
     * This assumes cities have a direct country_id.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
