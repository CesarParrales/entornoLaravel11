<div class="relative">
    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    <div class="relative mt-1">
        <input 
            type="text"
            id="{{ $inputId }}"
            wire:model.live.debounce.300ms="searchTerm"
            wire:focus="showDropdown = true"
            placeholder="{{ $placeholder }}"
            class="block w-full h-9 px-3 py-2 pr-10 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-amber-500 focus:ring-amber-500 sm:text-sm" {{-- Added h-9 --}}
            autocomplete="off"
        >
        @if ($selectedUserId)
            <button 
                type="button" 
                wire:click="clearSelection" 
                class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                aria-label="Limpiar selección"
            >
                <!-- Heroicon name: solid/x -->
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>

    @if ($showDropdown && $searchResults->isNotEmpty())
        <ul wire:transition class="absolute z-10 w-full mt-1 overflow-auto text-base bg-white rounded-md shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm dark:bg-gray-700 dark:ring-gray-600">
            @foreach ($searchResults as $user)
                <li 
                    wire:click="selectUser({{ $user->id }}, '{{ trim($user->name . ' ' . $user->last_name_paternal . ($user->username ? ' (' . $user->username . ')' : '')) }}')"
                    class="relative py-2 pl-3 text-gray-900 cursor-default select-none pr-9 hover:bg-amber-500 hover:text-white dark:text-gray-200 dark:hover:bg-amber-600"
                    role="option"
                    tabindex="-1"
                >
                    <span class="block truncate">
                        {{ $user->name }} {{ $user->last_name_paternal }} ({{ $user->username }}) - <em>{{ $user->email }}</em>
                    </span>
                </li>
            @endforeach
        </ul>
    @elseif ($showDropdown && strlen($searchTerm) >= 2 && $searchResults->isEmpty())
         <div class="absolute z-10 w-full p-3 mt-1 text-sm text-gray-500 bg-white border border-gray-300 rounded-md shadow-lg dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
            No se encontraron usuarios.
        </div>
    @endif
</div>
