<div>
    <div class="container px-4 py-8 mx-auto">
        @if ($product)
            <div class="lg:flex lg:space-x-8">
                <!-- Columna de Imagen -->
                <div class="mb-6 lg:w-1/2 lg:mb-0">
                    @if ($product->main_image_url)
                        <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}" class="object-cover w-full h-auto rounded-lg shadow-lg">
                    @else
                        <img src="{{ url('/images/default_product_placeholder.png') }}" alt="Producto sin imagen" class="object-cover w-full h-auto rounded-lg shadow-lg">
                    @endif
                </div>

                <!-- Columna de Información y Acciones -->
                <div class="lg:w-1/2">
                    <div class="p-6 bg-white rounded-lg shadow-lg">
                        <h1 class="mb-2 text-3xl font-bold text-slate-800">{{ $product->name }}</h1>
                        <p class="mb-4 text-sm text-slate-500">SKU: {{ $product->sku ?? 'N/A' }} @if($product->category) | Categoría: <a href="{{ route('catalog.category', $product->category) }}" class="text-amber-600 hover:text-amber-700">{{ $product->category->name }}</a>@endif</p>
                        
                        @if($product->short_description)
                            <p class="mb-4 text-md text-slate-700">
                                {{ $product->short_description }}
                            </p>
                        @endif

                        <!-- Sección de Precios Actualizada -->
                        <div class="mb-4">
                            <p class="text-4xl font-extrabold text-amber-600">
                                ${{ number_format($this->displayPriceWithVat, 2) }}
                                <span class="text-lg font-normal text-slate-500">(IVA incluido)</span>
                            </p>
                            <p class="text-sm text-slate-600">
                                Precio base: ${{ number_format($this->displayPriceBeforeVat, 2) }}
                                + IVA ({{ number_format(config('custom_settings.vat_rate', 0.15) * 100, 0) }}%): ${{ number_format($this->displayVatAmount, 2) }}
                            </p>
                            @auth
                                @if(Auth::user()->hasRole('Socio Multinivel')) {{-- Ajustar el nombre del rol si es diferente --}}
                                    <p class="text-xs font-semibold text-green-600">(Precio Socio aplicado)</p>
                                @endif
                            @endauth
                        </div>
                        
                        @if($product->points_value > 0)
                            <p class="mb-6 text-lg font-semibold text-amber-500">{{ $product->points_value }} Puntos</p>
                        @endif

                        <!-- Lógica para Bundles -->
                        @if($product->product_type === 'bundle_fixed' && $product->fixedBundleItems->count())
                            <div class="p-4 mb-6 border rounded-md border-slate-200 bg-slate-50">
                                <h4 class="mb-2 text-md font-semibold text-slate-700">Este paquete incluye:</h4>
                                <ul class="space-y-1 text-sm list-disc list-inside text-slate-600">
                                    @foreach($product->fixedBundleItems as $item)
                                        <li>{{ $item->name }} (Cantidad: {{ $item->pivot->quantity }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($product->product_type === 'bundle_configurable')
                            <div class="p-4 mb-6 border rounded-md border-slate-200 bg-slate-50">
                                <h4 class="mb-2 text-md font-semibold text-slate-700">Personaliza tu paquete:</h4>
                                <p class="text-sm text-slate-600">
                                    Selecciona 
                                    @if($product->min_configurable_items == $product->max_configurable_items)
                                        exactamente {{ $product->min_configurable_items }} producto(s)
                                    @else
                                        entre {{ $product->min_configurable_items ?? 1 }} y {{ $product->max_configurable_items ?? $product->configurableBundleOptions->count() }} productos
                                    @endif
                                    de la siguiente lista.
                                </p>
                                {{-- 
                                <p class="text-xs text-slate-500 mb-3">(La suma de los precios de los ítems seleccionados debe ser al menos ${{ number_format($product->base_price, 2) }})</p>
                                --}}
                                
                                @if($product->configurableBundleOptions->count())
                                    <div class="mt-4 space-y-3">
                                        @foreach($product->configurableBundleOptions as $option)
                                            <label for="option_{{ $option->id }}" class="flex items-center p-3 transition-colors border rounded-md cursor-pointer border-slate-300 hover:bg-slate-100">
                                                <input 
                                                    id="option_{{ $option->id }}"
                                                    type="checkbox" 
                                                    wire:model.live="selectedConfigurableOptions.{{ $option->id }}"
                                                    value="{{ $option->id }}"
                                                    class="w-5 h-5 rounded text-amber-600 border-slate-400 focus:ring-amber-500"
                                                    @if($this->selectedConfigurableItemsCount() >= ($product->max_configurable_items ?? $product->configurableBundleOptions->count()) && !isset($selectedConfigurableOptions[$option->id])) disabled @endif
                                                >
                                                <span class="ml-3 text-sm text-slate-700">{{ $option->name }}</span>
                                                <span class="ml-auto text-sm font-medium text-slate-800">${{ number_format($option->current_price, 2) }}</span> <!-- Usar current_price del producto opción -->
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 text-sm">
                                        <p>Seleccionados: <span class="font-semibold">{{ $this->selectedConfigurableItemsCount() }}</span> / {{ $product->max_configurable_items ?? $product->configurableBundleOptions->count() }}</p>
                                        {{--
                                        <p>Precio de selección: <span class="font-semibold">${{ number_format($this->selectedConfigurableItemsPrice(), 2) }}</span> (Precio del paquete: ${{ number_format($product->base_price, 2) }})</p>
                                        --}}
                                        @if(!$this->configurableBundleSelectionIsValid())
                                            <p class="mt-1 text-xs text-red-600">
                                                @if(session()->has('error_configurable_bundle'))
                                                    {{ session('error_configurable_bundle') }}
                                                @else
                                                    @if($this->selectedConfigurableItemsCount() < ($product->min_configurable_items ?? 1))
                                                        Debes seleccionar al menos {{ $product->min_configurable_items ?? 1 }} producto(s).
                                                    @elseif($this->selectedConfigurableItemsCount() > ($product->max_configurable_items ?? $product->configurableBundleOptions->count()))
                                                        Has excedido el máximo de {{ $product->max_configurable_items ?? $product->configurableBundleOptions->count() }} productos.
                                                    @endif
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <p class="mt-3 text-sm text-slate-500">No hay opciones configurables definidas para este paquete.</p>
                                @endif
                            </div>
                        @endif

                        <!-- Selección de cantidad y añadir al carrito -->
                        <div class="mt-6">
                            @if($product->product_type !== 'bundle_configurable')
                                <label for="quantity" class="block mb-1 text-sm font-medium text-slate-700">Cantidad</label>
                                <input wire:model.defer="quantity" type="number" id="quantity" name="quantity" value="1" min="1" class="w-24 px-3 py-2 border rounded-md shadow-sm border-slate-300 focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                            @else
                                <input wire:model.defer="quantity" type="hidden" value="1"> <!-- Para bundles configurables, la cantidad es 1 del paquete -->
                            @endif
                        </div>

                        <button
                            type="button"
                            wire:click="addToCart"
                            @if($product->product_type === 'bundle_configurable' && !$this->configurableBundleSelectionIsValid()) disabled @endif
                            class="w-full px-6 py-3 mt-6 font-bold text-white transition-colors duration-300 bg-amber-500 rounded-lg hover:bg-amber-600 text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            @if($product->product_type === 'bundle_configurable')
                                Añadir Paquete Configurado al Carrito
                            @else
                                Añadir al Carrito
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pestañas para más detalles -->
            <div class="mt-12">
                <div x-data="{ activeTab: 'description' }" class="p-6 bg-white rounded-lg shadow-lg">
                    <div class="mb-4 border-b border-slate-200">
                        <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'description'" :class="{ 'border-amber-500 text-amber-600': activeTab === 'description', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'description' }" class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2">
                                Descripción Completa
                            </button>
                            @if($product->ingredients)
                            <button @click="activeTab = 'ingredients'" :class="{ 'border-amber-500 text-amber-600': activeTab === 'ingredients', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'ingredients' }" class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2">
                                Ingredientes
                            </button>
                            @endif
                            @if($product->properties)
                            <button @click="activeTab = 'properties'" :class="{ 'border-amber-500 text-amber-600': activeTab === 'properties', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'properties' }" class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2">
                                Propiedades
                            </button>
                            @endif
                            @if($product->content_details)
                            <button @click="activeTab = 'specs'" :class="{ 'border-amber-500 text-amber-600': activeTab === 'specs', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'specs' }" class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2">
                                Contenido y Especificaciones
                            </button>
                            @endif
                        </nav>
                    </div>
                    <div>
                        <div x-show="activeTab === 'description'" class="prose max-w-none text-slate-600">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                        @if($product->ingredients)
                        <div x-show="activeTab === 'ingredients'" class="prose max-w-none text-slate-600">
                            <p>{{ $product->ingredients }}</p>
                        </div>
                        @endif
                        @if($product->properties)
                        <div x-show="activeTab === 'properties'" class="prose max-w-none text-slate-600">
                            <p>{{ $product->properties }}</p>
                        </div>
                        @endif
                        @if($product->content_details)
                        <div x-show="activeTab === 'specs'" class="prose max-w-none text-slate-600">
                            <p>{{ $product->content_details }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <p class="py-12 text-xl text-center text-slate-700">Producto no encontrado.</p>
        @endif
    </div>
</div>
