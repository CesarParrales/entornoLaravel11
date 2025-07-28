<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Illuminate\Support\Facades\Auth; // Importar Auth facade

class UserSearchSelect extends Component
{
    public string $searchTerm = '';
    public Collection $searchResults;
    public ?int $selectedUserId = null;
    public string $selectedUserName = ''; // Para mostrar en el input después de seleccionar

    // Parámetros pasados al componente
    public string $inputId = ''; // ID único para el input HTML
    public string $inputName = ''; // Atributo name para el input oculto que contendrá el ID
    public string $label = '';
    public string $placeholder = 'Buscar...';
    public string $eventNameToEmit = ''; // Evento a emitir al componente padre

    public bool $showDropdown = false;

    public function mount(string $inputId, string $inputName, string $label, string $eventNameToEmit, ?string $placeholder = null, ?int $initialSelectedUserId = null)
    {
        $this->inputId = $inputId;
        $this->inputName = $inputName; 
        $this->label = $label;
        $this->eventNameToEmit = $eventNameToEmit;
        if ($placeholder) {
            $this->placeholder = $placeholder;
        }
        $this->searchResults = collect();

        if ($initialSelectedUserId) {
            $user = User::find($initialSelectedUserId);
            if ($user) {
                // Construir un nombre representativo, usando el accesor getFullNameAttribute si existe, o first_name y last_name
                $displayName = $user->getFullNameAttribute() . ($user->username ? ' (' . $user->username . ')' : '');
                $this->selectUser($user->id, $displayName);
            }
        }
    }

    public function updatedSearchTerm()
    {
        if (strlen($this->searchTerm) < 2) {
            $this->searchResults = collect();
            $this->showDropdown = false;
            if (empty($this->searchTerm) && $this->selectedUserId) {
                // No limpiar la selección automáticamente al borrar el término si ya hay algo seleccionado,
                // el usuario debe usar clearSelection() explícitamente.
                // $this->clearSelection(); 
            }
            return;
        }

        // Si el searchTerm actual es igual al nombre del usuario seleccionado, no buscar.
        if ($this->selectedUserId && $this->searchTerm === $this->selectedUserName) {
            $this->showDropdown = false;
            $this->searchResults = collect();
            return;
        }


        $this->searchResults = User::where(function ($query) {
            $query->where('first_name', 'like', '%' . $this->searchTerm . '%') // Search in first_name
                  ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')  // Search in last_name
                  ->orWhere('username', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $this->searchTerm . '%')
                  // Consider searching in the concatenated full name if your DB supports it efficiently,
                  // or use the 'name' field if it's reliably populated with the full name.
                  // For now, searching first_name and last_name separately is a good start.
                  ->orWhere('name', 'like', '%' . $this->searchTerm . '%'); // Also search in the 'name' field as a fallback
        })
        ->when(Auth::check(), function ($query) { // Solo aplicar si hay un usuario autenticado (para el registro, podría no haberlo)
            $query->where('id', '!=', Auth::id()); // No permitir seleccionarse a sí mismo si está logueado
        })
        ->take(5) 
        ->get();
        
        $this->showDropdown = $this->searchResults->isNotEmpty();
    }

    public function selectUser(int $userId, string $userName)
    {
        $this->selectedUserId = $userId;
        $this->selectedUserName = $userName; 
        $this->searchTerm = $userName; 
        $this->searchResults = collect();
        $this->showDropdown = false;
        $this->dispatch($this->eventNameToEmit, userId: $userId);
    }

    public function clearSelection()
    {
        $this->selectedUserId = null;
        $this->selectedUserName = '';
        $this->searchTerm = '';
        $this->searchResults = collect();
        $this->showDropdown = false;
        $this->dispatch($this->eventNameToEmit, userId: null);
    }
    
    public function hideDropdown()
    {
        // Este método puede ser llamado con wire:blur.away en el input o contenedor principal
        // para ocultar el dropdown cuando se hace clic fuera.
        // Se necesita un pequeño delay para que el click en un item del dropdown se procese antes de ocultar.
        // Alternativamente, se puede manejar con Alpine.js para un control más fino.
        // Por ahora, lo dejamos así y el usuario puede hacer clic en un item o fuera.
        // Si se usa wire:blur o wire:focusout, puede que se oculte antes de seleccionar.
        // $this->showDropdown = false;
    }

    public function render()
    {
        return view('livewire.user-search-select');
    }
}
