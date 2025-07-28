<?php

namespace App\Livewire\Catalog;

use App\Models\Product;
use App\Services\CartService; // Importar CartService
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log; // Importar Log

#[Layout('layouts.app')]
class ShowProductCatalog extends Component
{
    use WithPagination;

    public string $searchTerm = '';
    public ?int $categoryId = null;

    protected $queryString = [
        'searchTerm' => ['except' => '', 'as' => 'q'],
        'categoryId' => ['except' => null, 'as' => 'category'],
    ];

    /**
     * Añade un producto al carrito.
     * Para productos simples y bundles fijos.
     * Bundles configurables deberían idealmente añadirse desde la página de detalle tras configuración.
     */
    public function addToCart(int $productId, CartService $cartService)
    {
        Log::debug("[ShowProductCatalog] addToCart called for productId: {$productId}");

        $product = Product::find($productId);

        if (!$product) {
            Log::warning("[ShowProductCatalog] Product not found for ID: {$productId}");
            $this->dispatch('notify', message: 'Producto no encontrado.', type: 'error');
            return;
        }

        Log::debug("[ShowProductCatalog] Product found: ID {$product->id}, Name: {$product->name}, Type: {$product->product_type}");

        if ($product->product_type === 'bundle_configurable') {
            Log::debug("[ShowProductCatalog] Product ID {$product->id} is bundle_configurable, redirecting to detail page.");
            return redirect()->route('catalog.product.detail', ['product' => $product->slug]);
        }

        Log::debug("[ShowProductCatalog] Attempting to add product ID {$product->id} ('{$product->name}') to cart. Type: {$product->product_type}");
        try {
            $cartService->add($product, 1); // Añade 1 unidad por defecto desde el catálogo
            $this->dispatch('cartUpdated');
            $this->dispatch('notify', message: "'{$product->name}' ha sido añadido a tu carrito.", type: 'success');
            Log::info("[ShowProductCatalog] Product ID {$product->id} ('{$product->name}') added to cart successfully.");
        } catch (\Exception $e) {
            Log::error("[ShowProductCatalog] Error adding product ID {$product->id} ('{$product->name}') to cart: " . $e->getMessage(), ['exception' => $e]);
            $this->dispatch('notify', message: 'Hubo un problema al añadir el producto al carrito.', type: 'error');
        }
    }

    public function render()
    {
        $products = Product::query()
            ->where('is_active', true)
            // ->whereIn('product_type', ['simple', 'bundle_fixed', 'bundle_configurable']) // Ya se maneja la lógica en addToCart
            ->when($this->searchTerm, function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term . '%')
                          ->orWhere('sku', 'like', '%' . $term . '%')
                          ->orWhere('short_description', 'like', '%' . $term . '%');
                });
            })
            ->when($this->categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.catalog.show-product-catalog', [
            'products' => $products,
        ]);
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedCategoryId()
    {
        $this->resetPage();
    }
}
