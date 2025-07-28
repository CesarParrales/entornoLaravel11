<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Para cambio de contraseña si se incluye aquí
use Illuminate\Validation\Rule; // Para unique ignore

#[Layout('layouts.app')]
class UserProfileForm extends Component
{
    // Propiedades del formulario vinculadas al usuario
    public string $first_name = '';
    public string $last_name = '';
    public string $username = '';
    public string $email = '';
    public string $phone = '';
    public ?string $birth_date = null;
    public string $gender = '';
    public string $dni = '';

    // Dirección
    public string $address_street = '';
    public string $address_city = '';
    public string $address_state = '';
    public string $address_postal_code = '';
    public ?int $address_country_id = null;

    // Para selectores
    public Collection $countries;
    public array $genders = [];

    public ?User $user;

    public function mount()
    {
        $this->user = Auth::user();

        if (!$this->user) {
            // Redirigir o mostrar error si no hay usuario autenticado
            return redirect()->route('login');
        }

        $this->first_name = $this->user->first_name ?? '';
        $this->last_name = $this->user->last_name ?? '';
        $this->username = $this->user->username ?? '';
        $this->email = $this->user->email ?? '';
        $this->phone = $this->user->phone ?? '';
        $this->birth_date = $this->user->birth_date ? $this->user->birth_date->format('Y-m-d') : null;
        $this->gender = $this->user->gender ?? '';
        $this->dni = $this->user->dni ?? '';

        $this->address_street = $this->user->address_street ?? '';
        $this->address_city = $this->user->address_city ?? '';
        $this->address_state = $this->user->address_state ?? '';
        $this->address_postal_code = $this->user->address_postal_code ?? '';
        $this->address_country_id = $this->user->address_country_id ?? null;

        $this->countries = Country::orderBy('name')->get(['id', 'name']);
        $this->genders = [
            'male' => 'Masculino',
            'female' => 'Femenino',
            'other' => 'Otro',
            'prefer_not_to_say' => 'Prefiero no decirlo',
        ];
    }

    protected function rules(): array
    {
        $userId = $this->user ? $this->user->id : null;

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [ // Generalmente el email no se permite cambiar fácilmente o requiere reverificación.
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|in:'.implode(',', array_keys($this->genders)),
            'dni' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'dni')->ignore($userId),
            ],
            'address_street' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:100',
            'address_state' => 'nullable|string|max:100',
            'address_postal_code' => 'nullable|string|max:20',
            'address_country_id' => 'nullable|exists:countries,id',
        ];
    }

    protected array $messages = [
        'first_name.required' => 'El nombre es obligatorio.',
        'last_name.required' => 'El apellido es obligatorio.',
        'username.required' => 'El nombre de usuario es obligatorio.',
        'username.unique' => 'Este nombre de usuario ya está en uso.',
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'El formato del correo no es válido.',
        'email.unique' => 'Este correo electrónico ya está en uso.',
        'dni.unique' => 'Esta identificación ya está en uso por otro usuario.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function saveProfile()
    {
        if (!$this->user) {
            session()->flash('error', 'No se pudo encontrar el usuario.');
            return;
        }

        $validatedData = $this->validate();

        try {
            $this->user->update($validatedData);
            
            // El accesor profile_completed se actualizará dinámicamente.
            // Si se quisiera persistir, se podría llamar aquí:
            // $this->user->profile_completed = $this->user->getProfileCompletedAttribute(null);
            // $this->user->saveQuietly(); // Para no disparar eventos de nuevo si no es necesario

            session()->flash('success', 'Perfil actualizado con éxito.');
            // Opcional: refrescar datos si algo más pudo haber cambiado por detrás
            // $this->mount(); 

        } catch (\Exception $e) {
            session()->flash('error', 'Hubo un error al actualizar el perfil: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.user-profile-form');
    }
}
