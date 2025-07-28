<div class="container px-4 py-8 mx-auto">
    <h1 class="mb-8 text-3xl font-semibold text-center text-gray-800 dark:text-gray-200">Crear Nueva Cuenta</h1>

    @if (session()->has('success_registration'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 border-l-4 border-green-500 dark:bg-green-200 dark:text-green-800" role="alert">
            <p class="font-bold">¡Éxito!</p>
            <p>{{ session('success_registration') }}</p>
        </div>
    @endif

    @if (session()->has('error_registration'))
        <div class="p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500 dark:bg-red-200 dark:text-red-800" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error_registration') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="register" class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Nombres -->
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombres</label>
                <input wire:model.lazy="first_name" type="text" id="first_name" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('first_name') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Apellido Paterno -->
            <div>
                <label for="last_name_paternal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primer Apellido</label>
                <input wire:model.lazy="last_name_paternal" type="text" id="last_name_paternal" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('last_name_paternal') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Apellido Materno -->
            <div>
                <label for="last_name_maternal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Segundo Apellido (Opcional)</label>
                <input wire:model.lazy="last_name_maternal" type="text" id="last_name_maternal" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('last_name_maternal') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- DNI/RUC -->
            <div>
                <label for="dni_ruc" class="block text-sm font-medium text-gray-700 dark:text-gray-300">DNI/RUC/Identificación</label>
                <input wire:model.lazy="dni_ruc" type="text" id="dni_ruc" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('dni_ruc') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div class="md:col-span-2">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
                <input wire:model.lazy="email" type="email" id="email" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('email') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Contraseña -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                <input wire:model.lazy="password" type="password" id="password" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('password') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Confirmación de Contraseña -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar Contraseña</label>
                <input wire:model.lazy="password_confirmation" type="password" id="password_confirmation" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
            </div>

            <!-- Teléfono Móvil -->
            <div>
                <label for="mobile_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono Móvil</label>
                <input wire:model.lazy="mobile_phone" type="tel" id="mobile_phone" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('mobile_phone') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Fecha de Nacimiento -->
            <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Nacimiento</label>
                <input wire:model.lazy="birth_date" type="date" id="birth_date" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('birth_date') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Género -->
            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Género</label>
                <select wire:model.lazy="gender" id="gender" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                    <option value="">Seleccione...</option>
                    @foreach($genders as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('gender') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- País -->
            <div>
                <label for="country_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">País de Residencia</label>
                <select wire:model.live="country_id" id="country_id" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                    <option value="">Seleccione un país...</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
                @error('country_id') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Provincia -->
            <div>
                <label for="address_province_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provincia/Estado</label>
                <select wire:model.live="address_province_id" id="address_province_id" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm" @if($provinces->isEmpty()) disabled @endif>
                    <option value="">Seleccione una provincia...</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                    @endforeach
                </select>
                @error('address_province_id') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Ciudad -->
            <div>
                <label for="address_city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad</label>
                <select wire:model.lazy="address_city_id" id="address_city_id" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm" @if($cities->isEmpty()) disabled @endif>
                    <option value="">Seleccione una ciudad...</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
                @error('address_city_id') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Dirección Línea 1 -->
            <div>
                <label for="address_line_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección (Calle y Número)</label>
                <input wire:model.lazy="address_line_1" type="text" id="address_line_1" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('address_line_1') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Código Postal -->
            <div>
                <label for="address_postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código Postal (Opcional)</label>
                <input wire:model.lazy="address_postal_code" type="text" id="address_postal_code" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('address_postal_code') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            
            <hr class="my-4 md:col-span-2 dark:border-gray-700">

            <!-- Tipo de Usuario -->
            <div>
                <label for="user_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quiero registrarme como</label>
                <select wire:model.live="user_type" id="user_type" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                    @foreach($userTypes as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('user_type') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Invitador -->
            <div>
                <livewire:user-search-select 
                    inputId="invitador_search_register" 
                    inputName="invitador_id"
                    label="Referido por (Usuario o Email)" 
                    eventNameToEmit="invitadorIdSelected"
                    placeholder="Buscar por nombre, apellido, usuario o email"
                    :initialSelectedUserId="$invitador_id"
                />
                @error('invitador_id') <span class="block mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Patrocinador (si es tipo Socio) -->
            @if ($user_type === 'socio')
                <div class="md:col-span-2"> <!-- Ajustado para ocupar más espacio si es necesario -->
                    <livewire:user-search-select 
                        inputId="patrocinador_search_register" 
                        inputName="patrocinador_id"
                        label="Patrocinador de Colocación (Usuario o Email)" 
                        eventNameToEmit="patrocinadorIdSelected"
                        placeholder="Buscar por nombre, apellido, usuario o email"
                        :initialSelectedUserId="$patrocinador_id"
                    />
                    @error('patrocinador_id') <span class="block mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            @else
                <!-- Placeholder para mantener el grid si el patrocinador no es visible -->
                <!-- <div class="hidden md:block"></div> -->
            @endif
            
            <div class="md:col-span-2">
                 <hr class="my-4 dark:border-gray-700">
            </div>

            <!-- Términos y Condiciones -->
            <div class="md:col-span-2">
                <label for="terms_accepted" class="flex items-center">
                    <input wire:model.lazy="terms_accepted" type="checkbox" id="terms_accepted" class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Acepto los <a href="#" class="text-amber-600 hover:underline">Términos y Condiciones</a> y la <a href="#" class="text-amber-600 hover:underline">Política de Privacidad</a>.</span>
                </label>
                @error('terms_accepted') <span class="block mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-8 text-right">
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="px-6 py-3 font-semibold text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-75 dark:bg-green-500 dark:hover:bg-green-600">
                <span wire:loading.remove wire:target="register">Crear Cuenta</span>
                <span wire:loading wire:target="register">Procesando...</span>
            </button>
        </div>
    </form>
</div>
