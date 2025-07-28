<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout; // Importar Layout

#[Layout('layouts.app')] // Especificar el layout aquí
class CartPage extends Component
{
    public Collection $cartItems;
    // Nuevas propiedades para los totales desglosados
    public float $cartSubtotalBeforeVat = 0;
    public float $cartTotalVat = 0;
    public float $cartGrandTotal = 0;
    public int $totalPointsValue = 0;


    protected CartService $cartService;

    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function mount()
    {
        $this->loadCartData();
    }

    protected function loadCartData()
    {
        $this->cartItems = $this->cartService->getContents();
        // Cargar los nuevos totales del servicio
        $this->cartSubtotalBeforeVat = $this->cartService->getCartSubtotalBeforeVat();
        $this->cartTotalVat = $this->cartService->getCartTotalVat();
        $this->cartGrandTotal = $this->cartService->getCartGrandTotal();
        $this->totalPointsValue = $this->cartService->getTotalPointsValue();
    }

    public function updateQuantity(string $cartItemId, int $newQuantity)
    {
        $quantity = max(1, $newQuantity);

        $this->cartService->update($cartItemId, $quantity);
        $this->loadCartData(); // Recarga todos los datos, incluyendo los nuevos totales
        $this->dispatch('cartUpdated'); 
        $this->dispatch('notify', message: 'Cantidad actualizada en el carrito.', type: 'success');
    }

    public function incrementQuantity(string $cartItemId)
    {
        // El $cartItemId ya es el ID correcto generado por generateCartItemId
        $item = $this->cartItems->get($cartItemId);
        if ($item) {
            $this->updateQuantity($cartItemId, $item['quantity'] + 1);
        }
    }

    public function decrementQuantity(string $cartItemId)
    {
        $item = $this->cartItems->get($cartItemId);
        if ($item && $item['quantity'] > 1) {
            $this->updateQuantity($cartItemId, $item['quantity'] - 1);
        } elseif ($item && $item['quantity'] <= 1) {
            $this->removeItem($cartItemId);
        }
    }

    public function removeItem(string $cartItemId)
    {
        $item = $this->cartItems->get($cartItemId); 
        $itemName = $item ? $item['name'] : 'Producto';

        $this->cartService->remove($cartItemId);
        $this->loadCartData();
        $this->dispatch('cartUpdated');
        $this->dispatch('notify', message: "'{$itemName}' eliminado del carrito.", type: 'info');
    }

    public function clearCart()
    {
        $this->cartService->clear();
        $this->loadCartData();
        $this->dispatch('cartUpdated');
        $this->dispatch('notify', message: 'El carrito ha sido vaciado.', type: 'info');
    }

    public function checkout()
    {
        if ($this->cartItems->isEmpty()) {
            // Usar session()->flash() para mensajes que persisten tras redirección si es necesario,
            // o dispatch para notificaciones en la misma página.
            $this->dispatch('notify', message: 'Tu carrito está vacío.', type: 'error');
            return;
        }
        // Redirige a la página de checkout. Asegúrate de que la ruta 'checkout.page' exista.
        return redirect()->route('checkout.page');
    }

    public function render()
    {
        // Los datos ya se cargan en mount y se actualizan en las acciones.
        // No es necesario volver a cargarlos aquí a menos que haya una razón específica.
        return view('livewire.cart-page');
    }
}
