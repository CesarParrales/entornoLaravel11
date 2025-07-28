<div class="container px-4 py-8 mx-auto">
    <h1 class="mb-8 text-3xl font-semibold text-center text-gray-800 dark:text-gray-200">Mi Perfil</h1>

    @if (session()->has('success'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 border-l-4 border-green-500 dark:bg-green-700 dark:text-green-100" role="alert">
            <p class="font-bold">¡Éxito!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500 dark:bg-red-700 dark:text-red-100" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="saveProfile" class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            
            <!-- Sección Información Personal -->
            <div class="md:col-span-2">
                <h2 class="mb-4 text-xl font-semibold text-gray-700 dark:text-gray-300">Información Personal</h2>
            </div>

            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombres</label>
                <input wire:model.lazy="first_name" type="text" id="first_name" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('first_name') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apellidos</label>
                <input wire:model.lazy="last_name" type="text" id="last_name" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('last_name') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de Usuario</label>
                <input wire:model.lazy="username" type="text" id="username" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('username') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
                <input wire:model.lazy="email" type="email" id="email" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cambiar el email puede requerir reverificación.</p>
                @error('email') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label for="dni" class="block text-sm font-medium text-gray-700 dark:text-gray-300">DNI/RUC/Identificación</label>
                <input wire:model.lazy="dni" type="text" id="dni" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('dni') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono Móvil</label>
                <input wire:model.lazy="phone" type="tel" id="phone" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('phone') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Nacimiento</label>
                <input wire:model.lazy="birth_date" type="date" id="birth_date" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('birth_date') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

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

            <!-- Sección Dirección -->
            <div class="mt-6 md:col-span-2">
                <h2 class="mb-4 text-xl font-semibold text-gray-700 dark:text-gray-300">Dirección de Residencia</h2>
            </div>

            <div class="md:col-span-2">
                <label for="address_street" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Calle y Número</label>
                <input wire:model.lazy="address_street" type="text" id="address_street" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('address_street') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label for="address_city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad</label>
                <input wire:model.lazy="address_city" type="text" id="address_city" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('address_city') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="address_state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provincia/Estado</label>
                <input wire:model.lazy="address_state" type="text" id="address_state" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('address_state') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="address_postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código Postal</label>
                <input wire:model.lazy="address_postal_code" type="text" id="address_postal_code" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                @error('address_postal_code') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="address_country_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">País</label>
                <select wire:model.lazy="address_country_id" id="address_country_id" class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                    <option value="">Seleccione...</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
                @error('address_country_id') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

        </div>

        <div class="mt-8 text-right">
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="px-6 py-3 font-semibold text-white bg-amber-600 rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 disabled:opacity-75 dark:bg-amber-500 dark:hover:bg-amber-600">
                <span wire:loading.remove wire:target="saveProfile">Guardar Cambios</span>
                <span wire:loading wire:target="saveProfile">Guardando...</span>
            </button>
        </div>
    </form>

    <!-- Placeholder for other profile sections like Change Password, MLM Info (read-only), etc. -->
    <!-- 
    <div class="mt-12">
        <h2 class="mb-4 text-xl font-semibold text-gray-700 dark:text-gray-300">Cambiar Contraseña</h2>
        <livewire:change-password-form /> 
    </div>
    -->
</div>
