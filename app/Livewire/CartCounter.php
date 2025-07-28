<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartCounter extends Component
{
    public int $itemCount = 0;

    protected $listeners = ['cartUpdated' => 'updateCartCount'];

    // Se inyecta CartService directamente en los métodos donde se necesita
    // para asegurar que siempre se use una instancia fresca, especialmente útil
    // si el servicio maneja estado que podría cambiar entre requests en un ciclo de vida complejo.
    // Sin embargo, para este caso, inyectar en mount y pasarla o resolverla de nuevo es también válido.

    public function mount()
    {
        // Resuelve CartService del contenedor de servicios de Laravel
        $cartService = app(CartService::class);
        $this->updateCartCount($cartService);
    }

    public function updateCartCount(CartService $cartService)
    {
        $this->itemCount = $cartService->getUniqueItemCount();
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
