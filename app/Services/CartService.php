<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth; // Added Auth facade
use Illuminate\Support\Facades\Log;

class CartService
{
    protected SessionManager $session;
    protected string $cartSessionKey = 'shopping_cart';
    protected string $roleSocio = 'Socio Multinivel'; // Define el nombre exacto del rol

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    /**
     * Añade un producto al carrito.
     *
     * @param Product $product El producto a añadir.
     * @param int $quantity La cantidad.
     * @param array|null $configuration Opciones de configuración para bundles personalizables.
     * @param float|null $customPrice Precio personalizado sin IVA (ej. para bundles configurados).
     * @return string ID del ítem en el carrito.
     */
    public function add(Product $product, int $quantity = 1, ?array $configuration = null, ?float $customPrice = null): string
    {
        $cart = $this->getCart();
        $cartItemId = $this->generateCartItemId($product, $configuration);

        $user = Auth::user();
        $unitPriceBeforeVat = 0;

        if (!is_null($customPrice)) {
            $unitPriceBeforeVat = $customPrice; // Custom price (sin IVA) tiene prioridad
        } elseif ($user && $user->hasRole($this->roleSocio)) {
            $unitPriceBeforeVat = $product->partner_price; // PVS sin IVA (usa accesor de Product)
        } else {
            $unitPriceBeforeVat = $product->current_price; // PVP sin IVA (usa accesor de Product)
        }

        $vatPerUnit = $product->calculateVat($unitPriceBeforeVat); // Usa método de Product

        if (isset($cart[$cartItemId])) {
            $cart[$cartItemId]['quantity'] += $quantity;
            // Recalcular totales del ítem si ya existe y solo se añade cantidad
            $cart[$cartItemId]['item_subtotal_before_vat'] = $cart[$cartItemId]['unit_price_before_vat'] * $cart[$cartItemId]['quantity'];
            $cart[$cartItemId]['item_total_vat'] = $cart[$cartItemId]['vat_per_unit'] * $cart[$cartItemId]['quantity'];
            $cart[$cartItemId]['item_grand_total'] = $cart[$cartItemId]['item_subtotal_before_vat'] + $cart[$cartItemId]['item_total_vat'];
        } else {
            $cart[$cartItemId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'unit_price_before_vat' => round($unitPriceBeforeVat, 2),
                'vat_per_unit' => round($vatPerUnit, 2),
                'item_subtotal_before_vat' => round($unitPriceBeforeVat * $quantity, 2),
                'item_total_vat' => round($vatPerUnit * $quantity, 2),
                'item_grand_total' => round(($unitPriceBeforeVat + $vatPerUnit) * $quantity, 2),
                'image_url' => $product->main_image_url,
                'configuration' => $configuration,
                'product_type' => $product->product_type,
                'points_value' => $product->points_value, // Añadido para validaciones
                'slug' => $product->slug, // Añadido para generar URLs en el carrito
            ];
        }

        $this->session->put($this->cartSessionKey, $cart);
        return $cartItemId;
    }

    /**
     * Actualiza la cantidad de un ítem en el carrito.
     */
    public function update(string $cartItemId, int $quantity): bool
    {
        $cart = $this->getCart();
        if (isset($cart[$cartItemId])) {
            if ($quantity > 0) {
                $cart[$cartItemId]['quantity'] = $quantity;
                // Recalcular totales del ítem
                $cart[$cartItemId]['item_subtotal_before_vat'] = round($cart[$cartItemId]['unit_price_before_vat'] * $quantity, 2);
                $cart[$cartItemId]['item_total_vat'] = round($cart[$cartItemId]['vat_per_unit'] * $quantity, 2);
                $cart[$cartItemId]['item_grand_total'] = $cart[$cartItemId]['item_subtotal_before_vat'] + $cart[$cartItemId]['item_total_vat'];

                $this->session->put($this->cartSessionKey, $cart);
                return true;
            } else {
                return $this->remove($cartItemId);
            }
        }
        return false;
    }

    public function remove(string $cartItemId): bool
    {
        $cart = $this->getCart();
        if (isset($cart[$cartItemId])) {
            unset($cart[$cartItemId]);
            $this->session->put($this->cartSessionKey, $cart);
            return true;
        }
        return false;
    }

    public function clear(): void
    {
        $this->session->forget($this->cartSessionKey);
    }

    public function getContents(): Collection
    {
        return new Collection($this->getCart());
    }

    protected function getCart(): array
    {
        return $this->session->get($this->cartSessionKey, []);
    }

    protected function generateCartItemId(Product $product, ?array $configuration = null): string
    {
        $id = 'product_' . $product->id;
        if (!empty($configuration)) {
            ksort($configuration);
            $id .= '_' . md5(json_encode($configuration));
        }
        return $id;
    }

    /**
     * Calcula el subtotal del carrito (suma de precios base de los ítems, sin IVA).
     */
    public function getCartSubtotalBeforeVat(): float
    {
        return round($this->getContents()->sum('item_subtotal_before_vat'), 2);
    }

    /**
     * Calcula el total de IVA para todos los ítems del carrito.
     */
    public function getCartTotalVat(): float
    {
        return round($this->getContents()->sum('item_total_vat'), 2);
    }

    /**
     * Calcula el gran total del carrito (Subtotal sin IVA + Total IVA).
     */
    public function getCartGrandTotal(): float
    {
        // return $this->getCartSubtotalBeforeVat() + $this->getCartTotalVat();
        return round($this->getContents()->sum('item_grand_total'), 2); // Más directo y menos propenso a errores de redondeo acumulados
    }

    public function getTotalItemUnits(): int
    {
        return $this->getContents()->sum('quantity');
    }

    public function getUniqueItemCount(): int
    {
        return $this->getContents()->count();
    }

    /**
     * Calcula el total de puntos de los ítems en el carrito.
     */
    public function getTotalPointsValue(): int
    {
        return $this->getContents()->sum(function ($item) {
            // Asegurarse de que 'points_value' y 'quantity' existan y sean numéricos
            $points = $item['points_value'] ?? 0;
            $quantity = $item['quantity'] ?? 0;
            return $points * $quantity;
        });
    }
}