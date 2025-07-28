<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Added
use Illuminate\Database\Eloquent\SoftDeletes; // Added for soft deletes
use Illuminate\Support\Facades\Storage; // Added Storage facade
use Illuminate\Support\Facades\Log; // Added Log facade
use Illuminate\Support\Facades\Config; // Added Config facade
use Carbon\Carbon; // Added Carbon for date comparisons

class Product extends Model
{
    use HasFactory, SoftDeletes; // Added SoftDeletes

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'product_type',
        'base_price',
        'points_value',
        'is_active',
        'is_featured',
        'category_id',
        // 'brand_id', // Uncomment if you add brands
        'ingredients',
        'properties',
        'content_details',
        'main_image_path',
        'is_on_promotion',
        'promotional_price',
        'promotion_start_date',
        'promotion_end_date',
        'pays_bonus',
        'bonus_amount',
        'min_configurable_items',
        'max_configurable_items',
        'is_registration_bundle', // Added
        'registration_bundle_price', // Added
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'points_value' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_on_promotion' => 'boolean',
        'promotional_price' => 'decimal:2',
        'promotion_start_date' => 'datetime',
        'promotion_end_date' => 'datetime',
        'pays_bonus' => 'boolean',
        'bonus_amount' => 'decimal:2',
        'min_configurable_items' => 'integer',
        'max_configurable_items' => 'integer',
        'is_registration_bundle' => 'boolean', // Added
        'registration_bundle_price' => 'decimal:2', // Added
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the prices for the product in different countries.
     */
    public function countryPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function getMainImageUrlAttribute(): ?string
    {
        // Log::debug("[Product Model] Accessing getMainImageUrlAttribute for Product ID: {$this->id}. main_image_path: '{$this->main_image_path}'");
        if ($this->main_image_path) {
            try {
                $url = Storage::disk('public')->url($this->main_image_path);
                // Log::debug("[Product Model] Product ID: {$this->id}. Generated URL: '{$url}' from path '{$this->main_image_path}'");
                return $url;
            } catch (\Exception $e) {
                // Log::error("[Product Model] Error generating URL for Product ID: {$this->id}, Path: '{$this->main_image_path}'. Error: " . $e->getMessage());
                return null; // Devuelve null si hay un error generando la URL
            }
        }
        // Log::debug("[Product Model] Product ID: {$this->id}. main_image_path is empty or null, returning null for URL.");
        return null;
    }

    /**
     * The products that are part of this fixed bundle.
     */
    public function fixedBundleItems(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_bundle_fixed_items', 'bundle_product_id', 'component_product_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * The products that are options for this configurable bundle.
     */
    public function configurableBundleOptions(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_bundle_configurable_options', 'bundle_product_id', 'option_product_id')
                    ->withPivot('default_quantity')
                    ->withTimestamps();
    }

    /**
     * Get the order items associated with the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // TODO: Add accessor for getAvailableStockAttribute() considering product_type and components for bundles.

    /**
     * Accessor for the current price (PVP), considering promotions.
     *
     * @return float
     */
    public function getCurrentPriceAttribute(): float
    {
        if (
            $this->is_on_promotion &&
            $this->promotional_price > 0 &&
            (!$this->promotion_start_date || Carbon::now()->gte($this->promotion_start_date)) &&
            (!$this->promotion_end_date || Carbon::now()->lte($this->promotion_end_date))
        ) {
            return (float) $this->promotional_price;
        }
        return (float) $this->base_price;
    }

    /**
     * Accessor for the partner price (PVS).
     *
     * @return float
     */
    public function getPartnerPriceAttribute(): float
    {
        $currentPrice = $this->current_price; // Uses the accessor above
        $discountPercentage = Config::get('custom_settings.partner_discount_percentage', 0.25);
        $discountAmount = $currentPrice * $discountPercentage;
        return round($currentPrice - $discountAmount, 2);
    }

    /**
     * Calculate VAT for a given price.
     *
     * @param float $price
     * @return float
     */
    public function calculateVat(float $price): float
    {
        $vatRate = Config::get('custom_settings.vat_rate', 0.15);
        return round($price * $vatRate, 2);
    }

    /**
     * Accessor for the PVP including VAT.
     *
     * @return float
     */
    public function getPvpWithVatAttribute(): float
    {
        $pvp = $this->current_price;
        $vatAmount = $this->calculateVat($pvp);
        return round($pvp + $vatAmount, 2);
    }

    /**
     * Accessor for the PVS including VAT.
     *
     * @return float
     */
    public function getPvsWithVatAttribute(): float
    {
        $pvs = $this->partner_price; // Uses the accessor
        $vatAmount = $this->calculateVat($pvs);
        return round($pvs + $vatAmount, 2);
    }
}
