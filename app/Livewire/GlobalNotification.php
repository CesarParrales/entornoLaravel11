<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On; // Para el nuevo listener de eventos

class GlobalNotification extends Component
{
    public ?string $message = null;
    public string $type = 'info'; // Tipos: success, error, warning, info
    public bool $show = false;

    // Escucha un evento global llamado 'notify'
    #[On('notify')] 
    public function showNotification(string $message, string $type = 'info')
    {
        $this->message = $message;
        $this->type = $type;
        $this->show = true;

        // No necesitamos $this->js() aquí si Alpine.js manejará el auto-ocultar en la vista.
    }

    // Este método podría ser llamado por Alpine si se necesita cerrar desde el backend
    public function hideNotification()
    {
        $this->show = false;
        // Opcional: resetear mensaje y tipo si se prefiere
        // $this->reset('message', 'type'); 
    }

    public function render()
    {
        return view('livewire.global-notification');
    }
}
