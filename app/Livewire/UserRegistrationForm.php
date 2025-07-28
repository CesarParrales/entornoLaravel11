<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\Province; // Added
use App\Models\City;     // Added
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\WalletService; // Added

#[Layout('layouts.app')]
class UserRegistrationForm extends Component
{
    // Datos del formulario
    public string $first_name = '';
    public string $last_name_paternal = '';
    public string $last_name_maternal = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $dni_ruc = '';
    public string $mobile_phone = '';
    public ?string $birth_date = null;
    public string $gender = '';
    public ?int $country_id = null;
    public ?int $address_province_id = null; // Changed from province
    public ?int $address_city_id = null;     // Changed from city
    public string $address_line_1 = '';
    public string $address_postal_code = '';
    public string $user_type = 'consumidor';

    public ?int $invitador_id = null;
    public ?int $patrocinador_id = null;

    public bool $terms_accepted = false;

    // Datos para selectores
    public Collection $countries;
    public Collection $provinces;
    public Collection $cities;
    public array $userTypes = [];
    public array $genders = [];

    protected $listeners = [
        'invitadorIdSelected' => 'setInvitadorId',
        'patrocinadorIdSelected' => 'setPatrocinadorId'
    ];

    public function mount()
    {
        $this->countries = Country::orderBy('name')->get(['id', 'name', 'iso_code_2']); // Removed 'phone_code'
        $this->provinces = collect(); // Initialize as empty collection
        $this->cities = collect();    // Initialize as empty collection

        $defaultCountry = $this->countries->firstWhere('iso_code_2', 'EC');
        if ($defaultCountry) {
            $this->country_id = $defaultCountry->id;
            $this->updatedCountryId($this->country_id); // Load provinces for default country
        } elseif ($this->countries->isNotEmpty()) {
            $this->country_id = $this->countries->first()->id;
            $this->updatedCountryId($this->country_id); // Load provinces for default country
        }

        $this->userTypes = [
            'consumidor' => 'Consumidor Registrado',
            'socio' => 'Socio Multinivel',
        ];
        $this->genders = [
            'male' => 'Masculino',
            'female' => 'Femenino',
            'other' => 'Otro',
            'prefer_not_to_say' => 'Prefiero no decirlo',
        ];
    }

    public function updatedCountryId($value)
    {
        if ($value) {
            $this->provinces = Province::where('country_id', $value)->orderBy('name')->get(['id', 'name']);
        } else {
            $this->provinces = collect();
        }
        $this->address_province_id = null; // Reset province
        $this->cities = collect();         // Reset cities
        $this->address_city_id = null;     // Reset city
    }

    public function updatedAddressProvinceId($value)
    {
        if ($value) {
            $this->cities = City::where('province_id', $value)->orderBy('name')->get(['id', 'name']);
        } else {
            $this->cities = collect();
        }
        $this->address_city_id = null; // Reset city
    }


    public function setInvitadorId(?int $userId = null) // Changed parameter
    {
        $this->invitador_id = $userId;
        $this->validateOnly('invitador_id');
    }

    public function setPatrocinadorId(?int $userId = null) // Changed parameter
    {
        $this->patrocinador_id = $userId;
        $this->validateOnly('patrocinador_id');
    }

    protected function rules(): array
    {
        $patrocinadorRules = 'nullable|exists:users,id';
        if ($this->user_type === 'socio') {
            $patrocinadorRules = 'required|exists:users,id';
        }

        return [
            'first_name' => 'required|string|max:100',
            'last_name_paternal' => 'required|string|max:100',
            'last_name_maternal' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'dni_ruc' => 'required|string|max:50|unique:users,dni',
            'mobile_phone' => 'required|string|max:50',
            'birth_date' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:'.implode(',', array_keys($this->genders)),
            'country_id' => 'required|exists:countries,id',
            'address_province_id' => 'required|exists:provinces,id', // Changed
            'address_city_id' => 'required|exists:cities,id',         // Changed
            'address_line_1' => 'required|string|max:255',
            'address_postal_code' => 'nullable|string|max:20',
            'user_type' => 'required|in:'.implode(',', array_keys($this->userTypes)),
            'invitador_id' => 'required|exists:users,id', // Removed 'different' rule
            'patrocinador_id' => $patrocinadorRules, // Removed 'different' rule
            'terms_accepted' => 'accepted',
        ];
    }

    protected array $messages = [
        'first_name.required' => 'El nombre es obligatorio.',
        'last_name_paternal.required' => 'El primer apellido es obligatorio.',
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'El formato del correo electrónico no es válido.',
        'email.unique' => 'Este correo electrónico ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'La confirmación de contraseña no coincide.',
        'dni_ruc.required' => 'El DNI/RUC/Identificación es obligatorio.',
        'dni_ruc.unique' => 'Esta identificación ya está registrada.',
        'mobile_phone.required' => 'El teléfono móvil es obligatorio.',
        'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
        'birth_date.date' => 'La fecha de nacimiento no es válida.',
        'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        'gender.required' => 'El género es obligatorio.',
        'country_id.required' => 'El país es obligatorio.',
        'address_province_id.required' => 'La provincia/estado es obligatoria.', // Changed
        'address_city_id.required' => 'La ciudad es obligatoria.',                 // Changed
        'address_line_1.required' => 'La dirección (calle y número) es obligatoria.',
        'address_postal_code.max' => 'El código postal no debe exceder los 20 caracteres.',
        'user_type.required' => 'Debe seleccionar un tipo de usuario.',
        'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones.',
        'invitador_id.required' => 'El referidor es obligatorio.',
        'invitador_id.exists' => 'El referidor seleccionado no es válido.',
        // 'invitador_id.different' => 'El referidor no puede ser el mismo que el patrocinador.', // Message removed
        'patrocinador_id.required' => 'El patrocinador es obligatorio para el tipo de usuario Socio.',
        'patrocinador_id.exists' => 'El patrocinador seleccionado no es válido.',
        // 'patrocinador_id.different' => 'El patrocinador no puede ser el mismo que el referidor.', // Message removed
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        if ($propertyName === 'country_id') {
            $this->updatedCountryId($this->country_id);
        }
        if ($propertyName === 'address_province_id') {
            $this->updatedAddressProvinceId($this->address_province_id);
        }
    }

    protected function generateUniqueUsername(string $firstName, string $lastNameP): string
    {
        $baseUsername = Str::slug($firstName . ' ' . $lastNameP, '');
        if (empty($baseUsername)) {
            $baseUsername = Str::lower(Str::random(8));
        }

        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        return $username;
    }

    public function register()
    {
        $validatedData = $this->validate();

        try {
            $username = $this->generateUniqueUsername($validatedData['first_name'], $validatedData['last_name_paternal']);
            $fullLastName = trim($validatedData['last_name_paternal'] . ' ' . ($validatedData['last_name_maternal'] ?? ''));

            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'last_name' => $fullLastName,
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'username' => $username,
                'dni' => $validatedData['dni_ruc'],
                'phone' => $validatedData['mobile_phone'],
                'birth_date' => $validatedData['birth_date'],
                'gender' => $validatedData['gender'],
                'address_country_id' => $validatedData['country_id'],
                'address_province_id' => $validatedData['address_province_id'], // Changed
                'address_city_id' => $validatedData['address_city_id'],         // Changed
                'address_street' => $validatedData['address_line_1'],
                'address_postal_code' => $validatedData['address_postal_code'] ?? null,
                'status' => 'pending_approval',
                'referrer_id' => $validatedData['invitador_id'],
                'sponsor_id' => $validatedData['patrocinador_id'] ?? null,
                'agreed_to_terms' => $validatedData['terms_accepted'],
            ]);

            $roleName = $validatedData['user_type'] === 'socio' ? 'Socio Multinivel' : 'Consumidor Registrado';
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->assignRole($role);

                // Ensure wallet is created if the user is a 'Socio Multinivel'
                if ($roleName === 'Socio Multinivel') {
                    app(WalletService::class)->ensureWalletExistsForSocio($user);
                }
            } else {
                Log::error("Rol '{$roleName}' no encontrado durante el registro del usuario {$user->email}.");
            }

            session()->flash('success_registration', '¡Registro exitoso! Revisa tu correo para los siguientes pasos o inicia sesión si tu cuenta no requiere aprobación.');

            $this->reset();
            $this->mount();

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Error de validación explícito en registro: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error durante el registro: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            session()->flash('error_registration', 'Hubo un error inesperado durante el registro. Por favor, inténtalo de nuevo o contacta a soporte.');
        }
    }

    public function render()
    {
        return view('livewire.user-registration-form');
    }
}
