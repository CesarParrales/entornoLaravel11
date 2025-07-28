<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_sku',
        'product_name',
        'quantity',
        'unit_price_before_vat', // Renamed from 'price'
        'item_subtotal_before_vat', // Renamed from 'subtotal'
        'item_vat_amount', // New
        'item_grand_total', // New
        'points_value_at_purchase', // New
        'product_pays_bonus_at_purchase', // Nuevo
        'product_bonus_amount_at_purchase', // Nuevo
        'options',
    ];

    protected $casts = [
        'options' => 'array',
        'unit_price_before_vat' => 'decimal:2',
        'item_subtotal_before_vat' => 'decimal:2',
        'item_vat_amount' => 'decimal:2',
        'item_grand_total' => 'decimal:2',
        'points_value_at_purchase' => 'integer',
        'product_pays_bonus_at_purchase' => 'boolean', // Nuevo
        'product_bonus_amount_at_purchase' => 'decimal:2', // Nuevo
    ];

    /**
     * El pedido al que pertenece este ítem.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * El producto asociado a este ítem de pedido.
     * Puede ser nulo si el producto original fue eliminado.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault([
            'name' => $this->product_name ?? 'Producto Eliminado', // Proporciona un nombre por defecto
            'sku' => $this->product_sku ?? 'N/A',
            // otros atributos por defecto si son necesarios para evitar errores en la vista
        ]);
    }
}
