<div class="container px-4 py-8 mx-auto">
    <h1 class="mb-8 text-3xl font-semibold text-center text-gray-800 dark:text-gray-200">Finalizar Compra</h1>

    @if (session()->has('error'))
        <div class="p-4 mb-6 text-red-700 bg-red-100 border-l-4 border-red-500 dark:bg-red-700 dark:text-red-100" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="p-4 mb-6 text-green-700 bg-green-100 border-l-4 border-green-500 dark:bg-green-700 dark:text-green-100" role="alert">
            <p class="font-bold">Éxito</p>
            <p>{{ session('success') }}</p>
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
        <form wire:submit.prevent="placeOrder">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Columna de Información del Cliente y Envío -->
                <div class="lg:col-span-2">
                    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                        <h2 class="mb-6 text-xl font-semibold text-gray-700 dark:text-gray-300">Información de Contacto y Envío</h2>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Completo</label>
                                <input wire:model.defer="customer_name" type="text" id="customer_name" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                @error('customer_name') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
                                <input wire:model.defer="customer_email" type="email" id="customer_email" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                @error('customer_email') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="customer_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono (Opcional)</label>
                                <input wire:model.defer="customer_phone" type="tel" id="customer_phone" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                @error('customer_phone') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="my-6 dark:border-gray-700">

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="shipping_address_line1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección (Línea 1)</label>
                                <input wire:model.defer="shipping_address_line1" type="text" id="shipping_address_line1" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                @error('shipping_address_line1') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="shipping_address_line2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección (Línea 2 - Opcional)</label>
                                <input wire:model.defer="shipping_address_line2" type="text" id="shipping_address_line2" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="shipping_city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad</label>
                                <input wire:model.defer="shipping_city" type="text" id="shipping_city" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                @error('shipping_city') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="shipping_state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado/Provincia</label>
                                <input wire:model.defer="shipping_state" type="text" id="shipping_state" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                @error('shipping_state') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código Postal</label>
                                <input wire:model.defer="shipping_postal_code" type="text" id="shipping_postal_code" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                @error('shipping_postal_code') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="shipping_country_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">País</label>
                                <select wire:model.defer="shipping_country_code" id="shipping_country_code" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                                    <option value="EC">Ecuador</option>
                                    <!-- Añadir más países si es necesario -->
                                </select>
                                @error('shipping_country_code') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="my-6 dark:border-gray-700">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notas Adicionales (Opcional)</label>
                            <textarea wire:model.defer="notes" id="notes" rows="3" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm"></textarea>
                            @error('notes') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <hr class="my-6 dark:border-gray-700">
                        <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Método de Pago</h3>
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border rounded-md cursor-pointer dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input wire:model.defer="payment_method" type="radio" name="payment_method" value="cash_on_delivery" class="w-5 h-5 text-amber-600 form-radio focus:ring-amber-500">
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Pago Contra Entrega</span>
                            </label>
                            <label class="flex items-center p-3 border rounded-md cursor-pointer dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input wire:model.defer="payment_method" type="radio" name="payment_method" value="bank_transfer" class="w-5 h-5 text-amber-600 form-radio focus:ring-amber-500">
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Transferencia Bancaria</span>
                            </label>
                            @error('payment_method') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Para la integración con Stripe/Cashier, se añadiría aquí el formulario de tarjeta.</p>

                    </div>
                </div>

                <!-- Columna de Resumen del Pedido -->
                <div class="lg:col-span-1">
                    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
                        <h2 class="mb-6 text-xl font-semibold text-gray-700 dark:text-gray-300">Resumen de tu Pedido</h2>
                        <div class="space-y-3">
                            @foreach ($cartItems as $item)
                                <div class="flex items-center justify-between pb-2 border-b dark:border-gray-700">
                                    <div class="flex items-center">
                                        <img src="{{ $item['image_url'] ?? asset('images/default-product.png') }}" alt="{{ $item['name'] }}" class="object-cover w-12 h-12 mr-3 rounded">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                                            <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Cant: {{ $item['quantity'] }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Unit (s/IVA): ${{ number_format($item['unit_price_before_vat'], 2) }}
                                            </p>
                                             <p class="text-xs text-gray-500 dark:text-gray-400">
                                                IVA Unit: ${{ number_format($item['vat_per_unit'], 2) }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">${{ number_format($item['item_grand_total'], 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="pt-4 mt-4 border-t dark:border-gray-700">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Subtotal (sin IVA)</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($cartSubtotalBeforeVat, 2) }}</p>
                            </div>
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Envío</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($shippingCost, 2) }} <span class="text-xs">(Estimado)</span></p>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total IVA</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($cartTotalVat, 2) }} <span class="text-xs">({{ config('custom_settings.vat_rate') * 100 }}%)</span></p>
                            </div>
                            <div class="flex items-center justify-between text-lg font-bold text-gray-900 dark:text-white">
                                <p>Gran Total</p>
                                <p>${{ number_format($grandTotal, 2) }}</p>
                            </div>
                        </div>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="placeOrder"
                                class="w-full px-6 py-3 mt-6 font-semibold text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-75 dark:bg-green-500 dark:hover:bg-green-600">
                            <span wire:loading.remove wire:target="placeOrder">Realizar Pedido</span>
                            <span wire:loading wire:target="placeOrder">Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
