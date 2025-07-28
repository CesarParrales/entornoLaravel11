<?php

namespace App\Livewire\Catalog;

use App\Models\Product;
use App\Services\CartService; // Importar CartService
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth; // Added Auth facade
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class ProductDetail extends Component
{
    public Product $product;
    public array $selectedConfigurableOptions = [];
    public int $quantity = 1;

    protected string $roleSocio = 'Socio Multinivel'; // Definir el nombre exacto del rol

    public function mount(Product $product)
    {
        $this->product = $product->loadMissing(['category', 'fixedBundleItems', 'configurableBundleOptions']);
        
        if ($this->product->product_type === 'bundle_configurable') {
            // Inicialización de opciones configurables si es necesario
        }
    }

    #[Computed]
    public function displayPriceBeforeVat(): float
    {
        $user = Auth::user();
        if ($user && $user->hasRole($this->roleSocio)) {
            return $this->product->partner_price; // PVS sin IVA (usa accesor de Product)
        }
        return $this->product->current_price; // PVP sin IVA (usa accesor de Product)
    }

    #[Computed]
    public function displayVatAmount(): float
    {
        return $this->product->calculateVat($this->displayPriceBeforeVat); // Usa el computed property anterior
    }

    #[Computed]
    public function displayPriceWithVat(): float
    {
        return round($this->displayPriceBeforeVat + $this->displayVatAmount, 2);
    }


    #[Computed]
    public function selectedConfigurableItemsCount(): int
    {
        return count(array_filter($this->selectedConfigurableOptions));
    }

    #[Computed]
    public function selectedConfigurableItemsPrice(): float
    {
        $totalPrice = 0;
        if ($this->product->product_type === 'bundle_configurable' && !empty($this->selectedConfigurableOptions)) {
            $selectedOptionIds = array_keys(array_filter($this->selectedConfigurableOptions));
            if (!empty($selectedOptionIds)) {
                $selectedProductsAsOptions = $this->product->configurableBundleOptions->whereIn('id', $selectedOptionIds);
                $totalPrice = $selectedProductsAsOptions->sum(function($p) {
                    return $p->current_price; // Usar el accesor current_price
                });
            }
        }
        return $totalPrice;
    }
    
    #[Computed]
    public function configurableBundleSelectionIsValid(): bool
    {
        if ($this->product->product_type !== 'bundle_configurable') {
            return true; 
        }

        $count = $this->selectedConfigurableItemsCount();
        $maxItems = $this->product->max_configurable_items ?? $this->product->configurableBundleOptions->count();
        $minItems = $this->product->min_configurable_items ?? 1;

        if ($count < $minItems || $count > $maxItems) {
            session()->flash('error_configurable_bundle', "Debe seleccionar entre {$minItems} y {$maxItems} opciones.");
            return false;
        }
        return true;
    }

    public function addToCart(CartService $cartService)
    {
        if ($this->quantity < 1) {
            $this->quantity = 1;
        }

        $configurationDetails = null;
        $finalPrice = null; 

        if ($this->product->product_type === 'bundle_configurable') {
            if (!$this->configurableBundleSelectionIsValid()) {
                return;
            }
            $configurationDetails = [];
            foreach ($this->selectedConfigurableOptions as $optionId => $isSelected) {
                if ($isSelected) { 
                    $optionProduct = Product::find($optionId); 
                    if($optionProduct) {
                        $configurationDetails[$optionProduct->id] = $optionProduct->name;
                    }
                }
            }
             if (empty($configurationDetails)) {
                session()->flash('error_configurable_bundle', 'Por favor, seleccione las opciones para su paquete configurable.');
                return;
            }
        }

        try {
            // El CartService ahora determina el precio (PVP/PVS) y calcula el IVA internamente.
            // No es necesario pasar $finalPrice aquí a menos que sea un precio de bundle ya calculado con IVA.
            // Para productos simples/bundles fijos, $finalPrice puede ser null.
            // Para bundles configurables, el precio base del bundle se usa y el CartService lo tomará.
            $cartService->add($this->product, $this->quantity, $configurationDetails, null);
            $this->dispatch('cartUpdated');
            $this->dispatch('notify', message: "'{$this->product->name}' (x{$this->quantity}) ha sido añadido a tu carrito.", type: 'success');
            $this->quantity = 1; 
            $this->selectedConfigurableOptions = []; 
        } catch (\Exception $e) {
            Log::error("Error adding to cart from ProductDetail: " . $e->getMessage() . " Trace: " . $e->getTraceAsString());
            $this->dispatch('notify', message: 'Hubo un problema al añadir el producto al carrito.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.catalog.product-detail', [
            'product' => $this->product,
            // Las propiedades computadas estarán disponibles automáticamente en la vista
        ]);
    }
}
