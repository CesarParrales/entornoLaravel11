<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BonusType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_active',
        'calculation_type',
        'amount_fixed',
        'percentage_value',
        'points_to_currency_conversion_factor',
        'trigger_event',
        'configuration_details',
        'wallet_transaction_description_template',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amount_fixed' => 'decimal:2',
        'percentage_value' => 'decimal:4', // e.g., 0.0500 for 5%
        'points_to_currency_conversion_factor' => 'decimal:4', // e.g., 1.0000 for 1:1
        'configuration_details' => 'array',
    ];

    /**
     * Boot function from Laravel.
     * Ensures a slug is generated if not provided.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
            if (empty($model->slug) || $model->isDirty('name')) {
                $model->slug = Str::slug($model->name);
            }
        });
    }
}
