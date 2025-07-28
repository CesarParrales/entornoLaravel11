<div class="container px-4 py-8 mx-auto">
    <h1 class="mb-6 text-3xl font-semibold text-center text-gray-800 dark:text-gray-200">Tu Carrito de Compras</h1>

    @if (session()->has('info'))
        <div class="p-4 mb-4 text-blue-700 bg-blue-100 border-l-4 border-blue-500 dark:bg-blue-200 dark:text-blue-800" role="alert">
            <p class="font-bold">Información</p>
            <p>{{ session('info') }}</p>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500 dark:bg-red-200 dark:text-red-800" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if ($cartItems->isEmpty())
        <div class="p-6 text-center bg-white rounded-lg shadow-md dark:bg-gray-800">
            <p class="mb-4 text-xl text-gray-700 dark:text-gray-300">Tu carrito está vacío.</p>
            <a href="{{ route('catalog.index') }}" class="px-6 py-2 font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                Ir a la tienda
            </a>
        </div>
    @else
        <div class="overflow-x-auto bg-white rounded-lg shadow-md dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Producto</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Precio Unitario</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-300">Cantidad</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Total Ítem</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-300">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach ($cartItems as $itemId => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-16 h-16">
                                        <img class="object-cover w-16 h-16 rounded" src="{{ $item['image_url'] ?? asset('images/default_product_placeholder.png') }}" alt="{{ $item['name'] }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <a href="{{ route('catalog.product.detail', $item['slug'] ?? '#') }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $item['name'] }}
                                            </a>
                                        </div>
                                        
                                        @if (isset($item['product_type']) && $item['product_type'] === 'bundle_configurable' && !empty($item['configuration']))
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold">Configuración:</span>
                                                <ul class="ml-2 list-disc list-inside">
                                                @foreach($item['configuration'] as $optionId => $optionName)
                                                    <li>{{ $optionName }}</li>
                                                @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @php
                                            $productModel = null;
                                            if (isset($item['product_type']) && $item['product_type'] === 'bundle_fixed' && isset($item['product_id'])) {
                                                $productModel = \App\Models\Product::find($item['product_id']);
                                            }
                                        @endphp
                                        @if ($productModel && $productModel->product_type === 'bundle_fixed' && $productModel->fixedBundleItems->count())
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold">Incluye:</span>
                                                <ul class="ml-2 list-disc list-inside">
                                                    @foreach ($productModel->fixedBundleItems as $component)
                                                        <li>{{ $component->name }} (Cant: {{ $component->pivot->quantity }})</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Puntos: {{ $item['points_value'] ?? 0 }} c/u</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                <div>Precio: ${{ number_format($item['unit_price_before_vat'], 2) }}</div>
                                <div class="text-xs">IVA: ${{ number_format($item['vat_per_unit'], 2) }}</div>
                                <div class="font-semibold">Total Unit.: ${{ number_format($item['unit_price_before_vat'] + $item['vat_per_unit'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center">
                                    <button wire:click="decrementQuantity('{{ $itemId }}')" class="px-2 py-1 text-sm font-medium text-gray-600 bg-gray-200 rounded-l hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">-</button>
                                    <input type="number" wire:model.lazy="cartItems.{{ $itemId }}.quantity" wire:change="updateQuantity('{{ $itemId }}', $event.target.value)" class="w-16 px-2 py-1 text-sm text-center border-t border-b border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" min="1">
                                    <button wire:click="incrementQuantity('{{ $itemId }}')" class="px-2 py-1 text-sm font-medium text-gray-600 bg-gray-200 rounded-r hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">+</button>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-300">
                                <div>Subtotal: ${{ number_format($item['item_subtotal_before_vat'], 2) }}</div>
                                <div class="text-xs">IVA: ${{ number_format($item['item_total_vat'], 2) }}</div>
                                <div class="font-semibold">Total: ${{ number_format($item['item_grand_total'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-center whitespace-nowrap">
                                <button wire:click="removeItem('{{ $itemId }}')" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-col items-end mt-8 md:flex-row md:justify-between">
            <div class="mb-4 md:mb-0">
                <button wire:click="clearCart" class="px-6 py-2 font-semibold text-red-600 border border-red-600 rounded-md hover:bg-red-600 hover:text-white dark:text-red-400 dark:border-red-400 dark:hover:bg-red-500 dark:hover:text-white">
                    Vaciar Carrito
                </button>
                <a href="{{ route('catalog.index') }}" class="px-6 py-2 ml-2 font-semibold text-blue-600 border border-blue-600 rounded-md hover:bg-blue-600 hover:text-white dark:text-blue-400 dark:border-blue-400 dark:hover:bg-blue-500 dark:hover:text-white">
                    Continuar Comprando
                </a>
            </div>
            <div class="w-full text-right md:w-auto">
                <div class="text-lg text-gray-700 dark:text-gray-300">
                    Subtotal (sin IVA): <span class="font-semibold">${{ number_format($cartSubtotalBeforeVat, 2) }}</span>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Total IVA: <span class="font-semibold">${{ number_format($cartTotalVat, 2) }}</span>
                </div>
                <!-- Aquí podrías añadir descuentos, envío, etc. -->
                <div class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                    Gran Total (con IVA): <span class="font-extrabold">${{ number_format($cartGrandTotal, 2) }}</span>
                </div>
                 <div class="mt-1 text-md text-gray-700 dark:text-gray-300">
                    Puntos Totales: <span class="font-semibold">{{ $totalPointsValue }}</span>
                </div>
                <button wire:click="checkout" class="w-full px-6 py-3 mt-4 font-semibold text-white bg-green-600 rounded-md md:w-auto hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600">
                    Proceder al Pago
                </button>
            </div>
        </div>
    @endif
</div>
