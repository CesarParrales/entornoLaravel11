<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', // Added
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country_code',
        // Si se añaden campos de dirección de facturación, incluirlos aquí
        'subtotal',
        'shipping_cost',
        'taxes',
        'discount_amount',
        'total',
        'status',
        'payment_method',
        'payment_gateway',
        'payment_gateway_transaction_id',
        'paid_at',
        'notes',
        'payment_details', // Added
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'payment_details' => 'array', // Added
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'taxes' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * El usuario (cliente) que realizó el pedido.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Los ítems incluidos en este pedido.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Calcula el total de puntos generados por esta orden.
     *
     * @return int
     */
    public function getTotalPointsGeneratedAttribute(): int
    {
        // Suma los puntos de cada OrderItem asociado a esta Order
        // Asegurándose de que 'points_value_at_purchase' no sea null
        return $this->items()->sum('points_value_at_purchase') ?? 0;
    }

    /**
     * Genera un número de pedido único.
     *
     * @return string
     */
    public static function generateOrderNumber(): string
    {
        // Ejemplo: PREFIJO-AAMMDD-HHMMSS-XXXX (4 caracteres aleatorios)
        // Asegurarse de que sea suficientemente único para las necesidades del negocio.
        // Considerar revisar colisiones si el volumen de pedidos es muy alto en el mismo segundo.
        $prefix = config('custom_settings.order_prefix', 'ORD');
        do {
            $number = $prefix . '-' . date('ymd-His') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
        } while (static::where('order_number', $number)->exists()); // Verifica unicidad

        return $number;
    }
}
