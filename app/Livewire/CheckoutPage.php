<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product; // Asegúrate de importar Product
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Para logging de errores
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;

#[Layout('layouts.app')]
class CheckoutPage extends Component
{
    protected CartService $cartService;

    // Resumen del carrito
    public Collection $cartItems;
    public float $cartSubtotalBeforeVat = 0; // Renamed for clarity
    public float $cartTotalVat = 0;          // New
    public float $shippingCost = 0;          // Placeholder, podría calcularse basado en dirección/peso
    public float $grandTotal = 0;            // Renamed for clarity (Cart Grand Total + Shipping)

    // Campos del formulario
    public string $customer_name = '';
    public string $customer_email = '';
    public string $customer_phone = '';
    public string $shipping_address_line1 = '';
    public string $shipping_address_line2 = '';
    public string $shipping_city = '';
    public string $shipping_state = ''; // Provincia/Estado
    public string $shipping_postal_code = '';
    public string $shipping_country_code = 'EC'; // Default a Ecuador
    public string $notes = '';

    // Para el método de pago (simplificado por ahora)
    public string $payment_method = 'cash_on_delivery'; // Opciones: 'cash_on_delivery', 'bank_transfer'

    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function mount()
    {
        $this->cartItems = $this->cartService->getContents();

        if ($this->cartItems->isEmpty()) {
            session()->flash('info', 'Tu carrito está vacío. Añade productos antes de proceder al pago.');
            return redirect()->route('cart.page');
        }

        $this->cartSubtotalBeforeVat = $this->cartService->getCartSubtotalBeforeVat();
        $this->cartTotalVat = $this->cartService->getCartTotalVat();
        // Lógica para calcular envío iría aquí en una implementación más completa
        // $this->shippingCost = $this->calculateShipping();
        
        // El grandTotal del carrito ya incluye el IVA. Solo falta sumar el envío.
        $this->grandTotal = $this->cartService->getCartGrandTotal() + $this->shippingCost;

        if (Auth::check()) {
            $user = Auth::user();
            $this->customer_name = $user->name;
            $this->customer_email = $user->email;
            $this->customer_phone = $user->customer_phone ?? ''; // Asumiendo que tienes este campo en User
            $this->shipping_address_line1 = $user->address_line_1 ?? '';
            $this->shipping_city = $user->city ?? '';
            $this->shipping_state = $user->province ?? '';
            // $this->shipping_postal_code = $user->postal_code ?? ''; // Si lo tienes
            if ($user->country) {
                $this->shipping_country_code = $user->country->iso_code ?? 'EC'; // Asumiendo iso_code en Country
            }
        }
    }

    protected function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:25',
            'shipping_address_line1' => 'required|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country_code' => 'required|string|size:2', // ISO 3166-1 alpha-2
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash_on_delivery,bank_transfer',
        ];
    }

    protected array $messages = [
        'customer_name.required' => 'El nombre del cliente es obligatorio.',
        'customer_email.required' => 'El correo electrónico del cliente es obligatorio.',
        'customer_email.email' => 'El formato del correo electrónico no es válido.',
        'shipping_address_line1.required' => 'La línea 1 de la dirección de envío es obligatoria.',
        'shipping_city.required' => 'La ciudad de envío es obligatoria.',
        'shipping_state.required' => 'El estado/provincia de envío es obligatorio.',
        'shipping_postal_code.required' => 'El código postal de envío es obligatorio.',
        'shipping_country_code.required' => 'El país de envío es obligatorio.',
        'shipping_country_code.size' => 'El código de país debe tener 2 caracteres.',
        'payment_method.required' => 'Debes seleccionar un método de pago.',
    ];

    public function placeOrder()
    {
        $this->validate();

        if ($this->cartItems->isEmpty()) {
            session()->flash('error', 'Tu carrito está vacío. No se puede realizar el pedido.');
            return redirect()->route('cart.page');
        }
        
        try {
            $orderData = [
                'user_id' => Auth::id(),
                'customer_name' => $this->customer_name,
                'customer_email' => $this->customer_email,
                'customer_phone' => $this->customer_phone,
                'shipping_address_line1' => $this->shipping_address_line1,
                'shipping_address_line2' => $this->shipping_address_line2,
                'shipping_city' => $this->shipping_city,
                'shipping_state' => $this->shipping_state,
                'shipping_postal_code' => $this->shipping_postal_code,
                'shipping_country_code' => $this->shipping_country_code,
                'subtotal' => $this->cartService->getCartSubtotalBeforeVat(), // Corrected
                'shipping_cost' => $this->shippingCost, // Asume que shippingCost se calcula y actualiza
                'taxes' => $this->cartService->getCartTotalVat(),          // Corrected
                'discount_amount' => 0, // Placeholder para futuros descuentos
                'total' => $this->cartService->getCartGrandTotal() + $this->shippingCost, // Corrected
                'status' => 'pending', 
                'payment_method' => $this->payment_method,
                'notes' => $this->notes,
            ];

            if ($this->payment_method === 'cash_on_delivery') {
                $orderData['status'] = 'processing';
            } elseif ($this->payment_method === 'bank_transfer') {
                $orderData['status'] = 'pending_payment';
            }

            $order = Order::create($orderData);

            foreach ($this->cartItems as $itemId => $item) {
                // $product = Product::find($item['product_id']); // No es necesario si ya tenemos SKU en el item del carrito
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_sku' => Product::find($item['product_id'])->sku ?? 'N/A', // Obtener SKU actual
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price_before_vat' => $item['unit_price_before_vat'],
                    'item_subtotal_before_vat' => $item['item_subtotal_before_vat'],
                    'item_vat_amount' => $item['item_total_vat'], // Corrected field name from cart
                    'item_grand_total' => $item['item_grand_total'],
                    'points_value_at_purchase' => $item['points_value'] ?? 0,
                    'options' => $item['configuration'],
                ]);
            }

            $this->cartService->clear();
            $this->dispatch('cartUpdated'); // Para actualizar contadores de carrito, etc.

            session()->flash('success', "¡Pedido #{$order->id} realizado con éxito! Gracias por tu compra.");
            return redirect()->route('catalog.index'); 

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; 
        } catch (\Exception $e) {
            Log::error('Error al procesar el pedido: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            session()->flash('error', 'Hubo un error inesperado al procesar tu pedido. Por favor, inténtalo de nuevo o contacta a soporte.');
        }
    }

    public function render()
    {
        // Recalcular totales en cada render para reflejar cambios si los hubiera (ej. cambio de dirección afecta envío)
        // Aunque en este MVP, el envío es fijo.
        $this->cartSubtotalBeforeVat = $this->cartService->getCartSubtotalBeforeVat();
        $this->cartTotalVat = $this->cartService->getCartTotalVat();
        $this->grandTotal = $this->cartService->getCartGrandTotal() + $this->shippingCost;
        
        return view('livewire.checkout-page');
    }
}
