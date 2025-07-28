<div>
    <div class="container px-4 py-8 mx-auto">
        <h1 class="mb-8 text-3xl font-bold text-center">Nuestro Catálogo</h1>

        <!-- Barra de Búsqueda y Filtros (Placeholder) -->
        <div class="mb-6">
            <input 
                wire:model.live.debounce.300ms="searchTerm" 
                type="text" 
                placeholder="Buscar productos por nombre, SKU..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            />
            <!-- Aquí se podrían añadir selects para categorías, etc. -->
        </div>

        @if($products->count() > 0)
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <div class="overflow-hidden transition-transform duration-300 transform bg-white border border-gray-200 rounded-lg shadow-md hover:scale-105">
                        <a href="{{ route('catalog.product.detail', $product) }}">
                            @if ($product->main_image_url)
                                <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}" class="object-cover w-full h-56">
                            @else
                                <img src="{{ url('/images/default_product_placeholder.png') }}" alt="Producto sin imagen" class="object-cover w-full h-56">
                            @endif
                        </a>
                        <div class="p-4">
                            <h3 class="mb-1 text-lg font-semibold text-gray-800">
                                <a href="{{ route('catalog.product.detail', $product) }}" class="hover:text-amber-600">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <p class="mb-2 text-xs text-gray-500">SKU: {{ $product->sku ?? 'N/A' }}</p>
                            
                            @if($product->short_description)
                                <p class="h-16 mb-3 overflow-hidden text-sm text-gray-600">
                                    {{ Str::limit($product->short_description, 80) }}
                                </p>
                            @endif

                            <!-- Precios Actualizados -->
                            <div class="mb-3">
                                @php
                                    $user = Auth::user();
                                    $roleSocio = 'Socio Multinivel'; // Asegúrate que este sea el nombre exacto del rol
                                    $isSocio = $user && $user->hasRole($roleSocio);
                                    
                                    $displayPriceWithVat = $isSocio ? $product->pvs_with_vat : $product->pvp_with_vat;
                                    $basePriceBeforeVat = $isSocio ? $product->partner_price : $product->current_price;
                                    $vatRate = config('custom_settings.vat_rate', 0.15);
                                @endphp
                                <span class="text-xl font-bold text-gray-900">${{ number_format($displayPriceWithVat, 2) }}</span>
                                <p class="text-xs text-slate-500">
                                    (Precio base: ${{ number_format($basePriceBeforeVat, 2) }} + IVA {{ $vatRate * 100 }}%)
                                    @if($isSocio)
                                        <br><span class="font-semibold text-green-600">Precio Socio</span>
                                    @endif
                                </p>
                            </div>
                            
                            @if($product->points_value > 0)
                                <div class="mb-3 text-sm font-semibold text-amber-600">{{ $product->points_value }} Puntos</div>
                            @endif
                            
                            <a href="{{ route('catalog.product.detail', $product) }}" 
                               class="block w-full px-4 py-2 font-semibold text-center text-white transition-colors duration-300 bg-amber-500 rounded-lg hover:bg-amber-600">
                                Ver Detalles
                            </a>
                            <button wire:click="addToCart({{ $product->id }})" class="block w-full px-4 py-2 mt-2 font-semibold text-center text-white transition-colors duration-300 bg-green-500 rounded-lg hover:bg-green-600">
                                Añadir al Carrito
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2zm3-9V3a2 2 0 00-2-2H5a2 2 0 00-2 2v4M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos</h3>
                <p class="mt-1 text-sm text-gray-500">
                    No se encontraron productos que coincidan con tu búsqueda o no hay productos disponibles en este momento.
                </p>
            </div>
        @endif
    </div>
</div>
