<div class="container mx-auto p-4">

    {{-- Modal de Selección de País --}}
    @if ($showCountryModal)
        <div class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-xl max-w-2xl w-full">
                <h2 class="text-2xl font-semibold mb-6 text-center">Seleccione un país</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @forelse ($countries as $country)
                        <button wire:click="selectCountry({{ $country->id }})"
                            class="p-4 border rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 flex flex-col items-center">
                            {{-- Usar iso_code_2 para mostrar la bandera (ej. con un servicio externo o localmente) --}}
                            {{-- Ejemplo simple mostrando el código ISO si no hay imagen directa --}}
                            <div class="h-12 w-20 flex items-center justify-center mb-2 text-gray-600 border border-gray-300 bg-gray-100 text-xs">
                                {{-- Aquí podrías intentar construir una URL si usas un servicio como flagcdn.com:
                                <img src="https://flagcdn.com/w160/{{ strtolower($country->iso_code_2) }}.png" alt="{{ $country->name }}" class="h-12 w-20 object-contain">
                                O simplemente mostrar el código ISO como fallback o si no hay servicio de banderas: --}}
                                {{ $country->iso_code_2 ?? 'N/A' }}
                            </div>
                            <span class="text-sm font-medium text-center">{{ $country->name }}</span>
                        </button>
                    @empty
                        <p class="col-span-full text-center text-gray-500">No hay países disponibles.</p>
                    @endforelse
                </div>
                {{-- Botón Cerrar (opcional, ya que seleccionar un país cierra el modal) --}}
                {{-- <div class="mt-6 text-center">
                    <button wire:click="$set('showCountryModal', false)" class="text-gray-600 hover:text-gray-800">Cerrar</button>
                </div> --}}
            </div>
        </div>
    @endif

    {{-- Modal de Selección de Paquete Promocional --}}
    @if ($showActivationBundleModal)
        <div class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">Seleccione un paquete promocional</h2>
                    <button wire:click="skipActivationBundle" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                @if ($availableActivationBundles->isNotEmpty())
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">Puede seleccionar uno de nuestros paquetes de activación o continuar para elegir productos individualmente.</p>
                        <select wire:model.live="selectedActivationBundleId" class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Ningún paquete --</option>
                            @foreach ($availableActivationBundles as $bundle)
                                <option value="{{ $bundle->id }}">
                                    {{ $bundle->name }}
                                    ({{ $bundle->points_value }} pts)
                                    (${{ number_format($bundle->registration_bundle_price ?? $bundle->base_price, 2) }})
                                </option>
                            @endforeach
                        </select>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button wire:click="skipActivationBundle"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Omitir y elegir productos
                            </button>
                            <button wire:click="selectActivationBundle({{ $selectedActivationBundleId }})"
                                @if(!$selectedActivationBundleId) disabled @endif
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                Seleccionar Paquete
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-center text-gray-500 py-4">No hay paquetes de activación disponibles para su país en este momento.</p>
                     <div class="mt-6 text-center">
                        <button wire:click="skipActivationBundle"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Continuar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Contenido Principal de la Página (cuando los modales están cerrados) --}}
    @if (!$showCountryModal && !$showActivationBundleModal)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Columna 1: Formulario de Datos del Socio --}}
            <div class="md:col-span-1 bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold mb-4">1. Tus Datos</h3>
                <div class="space-y-4">
                    {{-- Referidor y Patrocinador (Placeholder para UserSearchSelect) --}}
                    <div>
                        @livewire('user-search-select', [
                            'inputId' => 'invitador_search',
                            'inputName' => 'invitador_id_selected', // No se usa directamente para el form model, el componente padre lo maneja
                            'label' => 'Referidor (Quien invita)',
                            'placeholder' => 'Buscar referidor por nombre, usuario o email...',
                            'eventNameToEmit' => 'invitadorIdSelectedForOnboarding',
                            'initialSelectedUserId' => $invitador_id
                        ], key('invitador-search-select'))
                        {{-- El valor de $invitador_id se actualiza en el componente padre por el evento --}}
                        {{-- Para mostrar el error de validación del componente padre: --}}
                        @error('invitador_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        @livewire('user-search-select', [
                            'inputId' => 'patrocinador_search',
                            'inputName' => 'patrocinador_id_selected',
                            'label' => 'Patrocinador (Ubicación en la red)',
                            'placeholder' => 'Buscar patrocinador por nombre, usuario o email...',
                            'eventNameToEmit' => 'patrocinadorIdSelectedForOnboarding',
                            'initialSelectedUserId' => $patrocinador_id
                        ], key('patrocinador-search-select'))
                        @error('patrocinador_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <hr class="my-6">

                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">Nombres</label>
                        <input type="text" wire:model.lazy="first_name" id="first_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="last_name_paternal" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                        <input type="text" wire:model.lazy="last_name_paternal" id="last_name_paternal" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('last_name_paternal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="last_name_maternal" class="block text-sm font-medium text-gray-700">Apellido Materno (Opcional)</label>
                        <input type="text" wire:model.lazy="last_name_maternal" id="last_name_maternal" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('last_name_maternal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="dni_ruc" class="block text-sm font-medium text-gray-700">DNI/RUC/Identificación</label>
                        <input type="text" wire:model.lazy="dni_ruc" id="dni_ruc" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('dni_ruc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                     <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
                        <input type="date" wire:model.lazy="birth_date" id="birth_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('birth_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Género</label>
                        <select wire:model.lazy="gender" id="gender" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                            <option value="">Seleccione...</option>
                            @foreach($genders as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <hr class="my-6">
                    <h4 class="text-md font-semibold mb-2">Información de Contacto y Dirección</h4>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                        <input type="email" wire:model.lazy="email" id="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="mobile_phone" class="block text-sm font-medium text-gray-700">Teléfono Móvil</label>
                        <input type="tel" wire:model.lazy="mobile_phone" id="mobile_phone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('mobile_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    {{-- El país ya está seleccionado ($selectedCountryId) y no se muestra como un selector aquí, pero se usa para cargar provincias --}}
                    @if($selectedCountryId && $provinces->isNotEmpty())
                        <div>
                            <label for="address_province_id" class="block text-sm font-medium text-gray-700">Provincia/Estado</label>
                            <select wire:model.live="address_province_id" id="address_province_id" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                                <option value="">Seleccione Provincia/Estado...</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                            @error('address_province_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    @if($address_province_id && $cities->isNotEmpty())
                        <div>
                            <label for="address_city_id" class="block text-sm font-medium text-gray-700">Ciudad</label>
                            <select wire:model.lazy="address_city_id" id="address_city_id" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                                <option value="">Seleccione Ciudad...</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                            @error('address_city_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif
                     <div>
                        <label for="address_line_1" class="block text-sm font-medium text-gray-700">Dirección (Calle y Número)</label>
                        <input type="text" wire:model.lazy="address_line_1" id="address_line_1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('address_line_1') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="address_postal_code" class="block text-sm font-medium text-gray-700">Código Postal (Opcional)</label>
                        <input type="text" wire:model.lazy="address_postal_code" id="address_postal_code" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                        @error('address_postal_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <hr class="my-6">
                    <h4 class="text-md font-semibold mb-2">Crear Contraseña</h4>
                     <div>
                         <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                         <input type="password" wire:model.defer="password" id="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                         @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                     </div>
                     <div>
                         <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                         <input type="password" wire:model.lazy="password_confirmation" id="password_confirmation" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm leading-tight">
                     </div>
                </div>
            </div>

            {{-- Columna 2: Catálogo de Productos --}}
            <div class="md:col-span-2 bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold mb-4">2. Elige tus Productos</h3>
                @if($catalogProducts->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($catalogProducts as $product)
                            <div class="border rounded-lg p-4 flex flex-col">
                                <div class="flex-shrink-0 h-48 w-full bg-gray-200 rounded-md overflow-hidden">
                                    @if($product->main_image_url)
                                        <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-4 flex-grow">
                                    <h4 class="text-lg font-semibold text-gray-800">{{ $product->name }}</h4>
                                    <p class="text-sm text-gray-500 mt-1 h-10 overflow-hidden">{{ Str::limit($product->short_description, 50) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">SKU: {{ $product->sku }}</p>
                                </div>
                                <div class="mt-3">
                                    <p class="text-sm text-gray-600">Puntos: <span class="font-bold">{{ $product->points_value }}</span></p>
                                    {{-- Asumiendo que tienes accessors para partner_price y current_price en el modelo Product --}}
                                    <p class="text-lg font-bold text-gray-900">${{ number_format($product->partner_price ?? $product->base_price, 2) }}
                                        @if(isset($product->current_price) && $product->current_price != ($product->partner_price ?? $product->base_price))
                                            <span class="text-xs text-gray-500 line-through">${{ number_format($product->current_price, 2) }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <button type="button" wire:click="addToCart({{ $product->id }})"
                                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                                        wire:loading.attr="disabled" wire:target="addToCart({{ $product->id }})">
                                        <span wire:loading.remove wire:target="addToCart({{ $product->id }})">Añadir al Carrito</span>
                                        <span wire:loading wire:target="addToCart({{ $product->id }})">Añadiendo...</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No hay productos disponibles en el catálogo en este momento, o estamos cargando...</p>
                @endif
            </div>

            {{-- Columna 3 (o parte de Columna 2): Carrito de Compras --}}
            {{-- Para este layout, lo pondremos debajo del catálogo por simplicidad inicial --}}
            <div class="md:col-span-3 bg-white p-6 rounded-lg shadow mt-6">
                <h3 class="text-xl font-semibold mb-4">3. Tu Pedido</h3>

                @if ($cartItems->isNotEmpty())
                    <div class="divide-y divide-gray-200">
                        @foreach ($cartItems as $itemId => $item)
                            <div class="py-4 flex items-center">
                                <img src="{{ $item['image_url'] ?? asset('images/default_product_placeholder.png') }}"
                                     alt="{{ $item['name'] }}" class="h-16 w-16 object-cover rounded-md mr-4">
                                <div class="flex-grow">
                                    <h4 class="font-medium text-gray-800">{{ $item['name'] }}</h4>
                                    <p class="text-sm text-gray-500">
                                        Precio Unitario (PVS): ${{ number_format($item['unit_price_before_vat'], 2) }}
                                        @if(isset($item['configuration']['is_activation_bundle']) && $item['configuration']['is_activation_bundle'])
                                            <span class="text-xs text-green-600">(Paquete Activación)</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="w-20 text-center">
                                    {{-- TODO: Implementar actualización de cantidad --}}
                                    <input type="number" value="{{ $item['quantity'] }}" min="1"
                                           class="w-16 text-center border-gray-300 rounded-md shadow-sm"
                                           wire:change="updateQuantity('{{ $itemId }}', $event.target.value)">
                                </div>
                                <div class="w-24 text-right font-medium text-gray-800">
                                    ${{ number_format($item['item_subtotal_before_vat'], 2) }}
                                </div>
                                <div class="w-10 text-right">
                                    {{-- TODO: Implementar eliminación de ítem --}}
                                    <button wire:click="removeFromCart('{{ $itemId }}')" class="text-red-500 hover:text-red-700">&times;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 border-t pt-6">
                        <div class="space-y-1 text-sm text-gray-700">
                            <p class="flex justify-between">
                                <span>Puntos Totales:</span>
                                <span class="font-semibold">{{ $cartTotals['total_points'] ?? 0 }} pts</span>
                            </p>
                            <p class="flex justify-between">
                                <span>Total Descuento (PVP-PVS):</span>
                                <span class="font-semibold text-green-600">-${{ number_format($cartTotals['total_discount'] ?? 0.00, 2) }}</span>
                            </p>
                            <hr class="my-2">
                            <p class="flex justify-between">
                                <span>Subtotal (PVS):</span>
                                <span class="font-semibold">${{ number_format($cartTotals['subtotal_pvs'] ?? 0.00, 2) }}</span>
                            </p>
                            <p class="flex justify-between">
                                <span>IVA ({{ (config('custom_settings.vat_rate', 0.15) * 100) }}%):</span>
                                <span class="font-semibold">${{ number_format($cartTotals['vat_amount'] ?? 0.00, 2) }}</span>
                            </p>
                            <p class="flex justify-between">
                                <span>Costo de Envío:</span>
                                <span class="font-semibold">${{ number_format($cartTotals['shipping_cost'] ?? 0.00, 2) }}</span>
                            </p>
                            <p class="flex justify-between text-lg font-bold text-gray-900 mt-2">
                                <span>Total a Pagar:</span>
                                <span>${{ number_format($cartTotals['total_payable'] ?? 0.00, 2) }}</span>
                            </p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 py-4 text-center">Tu carrito de compras está vacío.</p>
                @endif

                <div class="mt-6"> {{-- Movido el div de método de entrega para que siempre se muestre --}}
                    <label for="delivery_method" class="block text-sm font-medium text-gray-700">Método de Entrega</label>
                    <select wire:model.live="delivery_method" id="delivery_method" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">Seleccione un método</option>
                        <option value="pickup">Retiro en Tienda</option>
                        <option value="courier">Courier</option>
                    </select>
                    @error('delivery_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label for="payment_method_selected" class="block text-sm font-medium text-gray-700">Método de Pago</label>
                    <select wire:model.live="payment_method_selected" id="payment_method_selected" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md leading-tight">
                        <option value="">Seleccione un método de pago...</option>
                        @foreach($paymentMethods as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('payment_method_selected') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model.defer="terms_accepted" class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700">Acepto los <a href="#" class="text-blue-600 hover:underline">Términos y Condiciones</a></span>
                    </label>
                    @error('terms_accepted') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                 <div class="mt-6 text-right">
                    <button type="button" wire:click="registerAndPlaceOrder"
                        class="px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                        wire:loading.attr="disabled" wire:target="registerAndPlaceOrder"
                        @disabled(!$this->canSubmitForm)>
                        <span wire:loading wire:target="registerAndPlaceOrder">Procesando...</span>
                        <span wire:loading.remove wire:target="registerAndPlaceOrder">Registrarse y Continuar al Pago</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
