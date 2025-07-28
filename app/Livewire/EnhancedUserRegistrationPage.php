<?php

namespace App\Livewire;

use App\Models\City;     // Added
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Province; // Added
use App\Models\User;
use App\Services\CartService; // Importar CartService
use App\Services\WalletService; // Para creación de billetera
use Illuminate\Support\Collection; // Added
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Importar Log
use Illuminate\Support\Facades\Mail; // Importar Mail
use App\Mail\WelcomePendingPaymentMail; // Importar Mailable
use App\Mail\OrderPlacedOfflinePaymentMail; // Importar Mailable
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout; // Importar el atributo Layout
use Spatie\Permission\Models\Role;


#[Layout('layouts.app')] // Especificar el layout principal de la aplicación
class EnhancedUserRegistrationPage extends Component
{
    // Estado de los modales
    public bool $showCountryModal = true;
    public bool $showActivationBundleModal = false;

    // Selección de país y bundles
    public $selectedCountryId = null; // Este será el mismo que el country_id del formulario de dirección
    public Collection $availableActivationBundles;
    public $selectedActivationBundleId = null;

    // Propiedades del formulario de usuario (adaptadas de UserRegistrationForm)
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
    // country_id para la dirección es $selectedCountryId
    public ?int $address_province_id = null;
    public ?int $address_city_id = null;
    public string $address_line_1 = '';
    public string $address_postal_code = '';
    // user_type se determinará por el flujo, no es un campo directo del form aquí.

    public ?int $invitador_id = null;
    public ?int $patrocinador_id = null;

    public bool $terms_accepted = false;

    // Datos para selectores
    public Collection $countries; // Ya se carga en mount
    public Collection $provinces;
    public Collection $cities;
    public array $genders = [];
    // public array $userTypes = []; // No se necesita aquí

    protected $listeners = [
        'invitadorIdSelectedForOnboarding' => 'setInvitadorId',
        'patrocinadorIdSelectedForOnboarding' => 'setPatrocinadorId'
    ];

    // Propiedades para el catálogo de productos
    public Collection $catalogProducts;

    // Propiedades para el carrito (se integrará con CartService más adelante)
    public Collection $cartItems;
    public array $cartTotals = []; // Se inicializa en getDefaultCartTotals

    // Método de entrega
    public $delivery_method = null;

    // Método de pago
    public ?string $payment_method_selected = null;
    public array $paymentMethods = [];

    public function getCanSubmitFormProperty(): bool
    {
        // Verificar campos básicos requeridos (puedes añadir más según tu `rules()`)
        $requiredFieldsFilled = !empty($this->first_name) &&
                                !empty($this->last_name_paternal) &&
                                !empty($this->email) &&
                                !empty($this->password) &&
                                !empty($this->password_confirmation) &&
                                !empty($this->dni_ruc) &&
                                !empty($this->mobile_phone) &&
                                !empty($this->birth_date) &&
                                !empty($this->gender) &&
                                !empty($this->selectedCountryId) && // País para dirección
                                !empty($this->address_province_id) &&
                                !empty($this->address_city_id) &&
                                !empty($this->address_line_1) &&
                                !empty($this->invitador_id) &&
                                !empty($this->patrocinador_id);

        $cartMeetsRequirements = $this->selectedActivationBundleId || ($this->cartTotals['total_points'] >= 20);
        
        return $requiredFieldsFilled &&
               $this->terms_accepted &&
               !empty($this->delivery_method) &&
               !empty($this->payment_method_selected) &&
               $cartMeetsRequirements;
    }

    public function mount()
    {
        $this->countries = Country::where('is_active', true)
                                ->orderBy('name')
                                ->get(['id', 'name', 'iso_code_2']);

        $this->provinces = collect();
        $this->cities = collect();
        $this->availableActivationBundles = collect();
        $this->catalogProducts = collect();
        $this->cartItems = collect(); // Inicializar como colección
        $this->cartTotals = $this->getDefaultCartTotals(); // Inicializar totales

        $this->genders = [
            'male' => 'Masculino',
            'female' => 'Femenino',
            'other' => 'Otro',
            'prefer_not_to_say' => 'Prefiero no decirlo',
        ];

        $this->paymentMethods = [
            // Llaves deben ser seguras para usarse como valor y en lógica
            'online_credit_card' => 'Tarjeta de Crédito/Débito (Online)',
            'offline_pos' => 'Tarjeta de Crédito/Débito (Punto de Venta)',
            'bank_deposit' => 'Depósito Bancario',
            'bank_transfer' => 'Transferencia Bancaria',
            'cash_pos' => 'Efectivo (Punto de Venta)',
        ];

        // No se preselecciona país aquí, se fuerza el modal.
        // Si se quisiera preseleccionar como en UserRegistrationForm:
        // $defaultCountry = $this->countries->firstWhere('iso_code_2', 'EC');
        // if ($defaultCountry) {
        //     $this->selectedCountryId = $defaultCountry->id;
        //     $this->updatedSelectedCountryId($this->selectedCountryId);
        // } elseif ($this->countries->isNotEmpty()) {
        //     $this->selectedCountryId = $this->countries->first()->id;
        //     $this->updatedSelectedCountryId($this->selectedCountryId);
        // }
        $this->refreshCartData(app(CartService::class)); // Cargar datos del carrito al montar
    }

    private function getDefaultCartTotals(): array
    {
        return [
            'total_points' => 0,
            'total_discount' => 0.00,
            'subtotal_pvs' => 0.00,
            'vat_amount' => 0.00,
            'shipping_cost' => 0.00, // Se calculará más adelante
            'total_payable' => 0.00,
        ];
    }

    public function refreshCartData(CartService $cartService)
    {
        $this->cartItems = $cartService->getContents(); // Usa getContents()

        $totalPoints = $cartService->getTotalPointsValue();
        $subtotalPVS = $cartService->getCartSubtotalBeforeVat();
        $vatAmount = $cartService->getCartTotalVat();
        // $grandTotalPVS = $cartService->getCartGrandTotal(); // PVS + IVA

        // Calcular el descuento Total (PVP - PVS)
        $totalDiscount = 0;
        foreach ($this->cartItems as $item) {
            // El item del carrito ya tiene 'unit_price_before_vat' que es PVS o PVP según el usuario
            // Necesitamos el PVP original para calcular el descuento si el precio unitario es PVS.
            // Esto es un poco complejo porque CartService ya aplicó la lógica de precios.
            // Para un cálculo preciso del descuento (PVP - PVS), necesitaríamos que CartService
            // almacene el PVP original o que lo calculemos aquí buscando el producto.
            // Por simplicidad y consistencia con la guía, asumimos que el descuento se calcula
            // basado en el precio actual del producto (PVP) y su precio de socio (PVS).
            $product = Product::find($item['product_id']);
            if ($product) {
                $pvpForItem = $product->current_price; // Accesor de PVP
                $pvsForItem = $product->partner_price; // Accesor de PVS
                $totalDiscount += ($pvpForItem - $pvsForItem) * $item['quantity'];
            }
        }
        
        // El costo de envío se manejará más adelante y dependerá del método de entrega
        $shippingCost = 0.00; // Placeholder, se actualizará con la lógica de delivery_method
        if ($this->delivery_method === 'courier') {
            // TODO: Obtener costo de envío real (ej. desde configuración o servicio)
            $shippingCost = 5.00; // Ejemplo
        }


        $this->cartTotals = [
            'total_points' => $totalPoints,
            'total_discount' => round(max(0, $totalDiscount), 2), // Asegurar que no sea negativo
            'subtotal_pvs' => round($subtotalPVS, 2),
            'vat_amount' => round($vatAmount, 2),
            'shipping_cost' => round($shippingCost, 2),
            'total_payable' => round($subtotalPVS + $vatAmount + $shippingCost, 2),
        ];
    }


    // Este método se llama cuando selectedCountryId cambia (después de que el usuario selecciona en el modal)
    // y también se usa para cargar las provincias para el formulario de dirección.
    public function updatedSelectedCountryId($value)
    {
        Log::debug("updatedSelectedCountryId: Country ID seleccionado: " . $value);
        if ($value) {
            $this->provinces = Province::where('country_id', $value)->orderBy('name')->get(['id', 'name']);
            Log::debug("Provincias cargadas: ", $this->provinces->toArray());
        } else {
            $this->provinces = collect();
            Log::debug("Country ID nulo, provincias reseteadas.");
        }
        $this->address_province_id = null; // Reset province
        $this->cities = collect();         // Reset cities
        $this->address_city_id = null;     // Reset city
    }

    public function updatedAddressProvinceId($value)
    {
        Log::debug("updatedAddressProvinceId: Province ID seleccionado: " . $value);
        if ($value) {
            $this->cities = City::where('province_id', $value)->orderBy('name')->get(['id', 'name']);
            Log::debug("Ciudades cargadas: ", $this->cities->toArray());
        } else {
            $this->cities = collect();
            Log::debug("Province ID nulo, ciudades reseteadas.");
        }
        $this->address_city_id = null; // Reset city
    }

    public function render()
    {
        return view('livewire.enhanced-user-registration-page');
    }

    public function selectCountry(int $countryId)
    {
        Log::debug("selectCountry: País seleccionado en modal ID: " . $countryId);
        $this->selectedCountryId = $countryId;
        $this->showCountryModal = false;

        // Llamar a updatedSelectedCountryId explícitamente para cargar provincias para el formulario
        // ya que la propiedad $selectedCountryId se actualiza aquí y el hook `updated` podría no
        // haberse disparado aún en el mismo ciclo de vida para cargar las provincias necesarias
        // antes de que se evalúe la vista.
        $this->updatedSelectedCountryId($this->selectedCountryId);


        // Cargar bundles de activación para el país seleccionado
        // Asumimos que los bundles no están directamente filtrados por país en el modelo Product por ahora,
        // sino que todos los 'is_registration_bundle' están disponibles.
        // Si se requiere filtrado por país, se necesitaría una relación o lógica adicional.
        $this->availableActivationBundles = Product::where('is_active', true)
                                                ->where('is_registration_bundle', true)
                                                // ->where('country_id', $this->selectedCountryId) // Si los bundles son específicos por país
                                                ->orderBy('name')
                                                ->get();

        if ($this->availableActivationBundles->isNotEmpty()) {
            $this->showActivationBundleModal = true;
        } else {
            // Si no hay bundles, se pasa directamente a la página principal (modales cerrados)
            $this->showActivationBundleModal = false;
        }

        // Resetear la selección de bundle anterior si se cambia de país
        $this->selectedActivationBundleId = null;

        // Cargar productos para el catálogo si ya no estamos en modales
        if (!$this->showCountryModal && !$this->showActivationBundleModal) {
            $this->loadCatalogProducts();
        }
    }

    public function loadCatalogProducts()
    {
        // Cargar todos los productos activos que no sean bundles de activación (esos se ofrecen aparte)
        // Podríamos añadir paginación aquí más adelante si es necesario.
        $this->catalogProducts = Product::where('is_active', true)
            // ->where('is_registration_bundle', false) // Opcional: excluir bundles si solo se muestran en el modal
            ->orderBy('name')
            ->get();
    }

    public function selectActivationBundle(int $productId, CartService $cartService)
    {
        $bundle = Product::find($productId);

        if ($bundle && $bundle->is_registration_bundle) {
            // Limpiar carrito antes de añadir un bundle de activación para evitar duplicados o conflictos
            $cartService->clear(); // Usa clear()
            // El método add de CartService espera un objeto Product, no un ID.
            $cartService->add($bundle, 1, ['is_activation_bundle' => true], $bundle->registration_bundle_price); // Pasamos el precio especial del bundle
            $this->selectedActivationBundleId = $productId;
            $this->refreshCartData($cartService);
        } else {
            // Manejar caso donde el bundle no se encuentra o no es un bundle de activación
            session()->flash('cart_error', 'El paquete promocional seleccionado no es válido.');
        }

        $this->showActivationBundleModal = false;
        if (!$this->showCountryModal && !$this->showActivationBundleModal) { // Si se cierran todos los modales
            $this->loadCatalogProducts();
        }
    }

    public function skipActivationBundle(CartService $cartService)
    {
        // Si se omite un bundle, y antes se había seleccionado uno, hay que quitarlo del carrito.
        // Esto asume que los bundles de activación son únicos en el carrito o se manejan especialmente.
        // Una lógica más simple es que `selectActivationBundle` siempre limpie el carrito antes.
        if ($this->selectedActivationBundleId) {
             // $cartService->removeItem($this->selectedActivationBundleId); // O una lógica más específica si es necesario
        }
        $this->selectedActivationBundleId = null;
        $this->showActivationBundleModal = false;
        // $this->refreshCartData($cartService); // Opcional, si omitir afecta el carrito
        if (!$this->showCountryModal && !$this->showActivationBundleModal) { // Si se cierran todos los modales
            $this->loadCatalogProducts();
        }
    }

    public function addToCart(int $productId, CartService $cartService)
    {
        try {
            $product = Product::findOrFail($productId);
            // No permitir añadir bundles de activación directamente desde el catálogo si ya se ofreció en modal
            if ($product->is_registration_bundle && $this->availableActivationBundles->isNotEmpty()) {
                 session()->flash('cart_message', 'Los paquetes de activación se seleccionan al inicio.');
                 return;
            }
            // El método add de CartService espera un objeto Product.
            $cartService->add($product, 1);
            $this->refreshCartData($cartService);
            session()->flash('cart_message', "'{$product->name}' añadido al carrito.");
        } catch (\Exception $e) {
            session()->flash('cart_error', 'Error al añadir el producto al carrito.');
            // Log::error("Error adding to cart: " . $e->getMessage());
        }
    }

    // TODO: Implementar updateQuantity, removeFromCart usando CartService y refreshCartData

    public function updatedDeliveryMethod($value)
    {
        // Simplemente volvemos a calcular todos los totales del carrito,
        // ya que refreshCartData ahora considera $this->delivery_method
        $this->refreshCartData(app(CartService::class));
    }

    // Fase 2: Lógica de Registro, Creación de Pedido y Flujo de Pago
    // ###################################################################

    protected function rules(): array
    {
        // Las reglas 'unique' para email y dni_ruc se validarán al momento de intentar crear el usuario
        // para evitar consultas a la BD en cada keystroke si se usa .lazy o .live.
        // Aquí se definen para la validación final.
        return [
            'first_name' => 'required|string|max:100',
            'last_name_paternal' => 'required|string|max:100',
            'last_name_maternal' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'dni_ruc' => 'required|string|max:50',
            'mobile_phone' => 'required|string|max:50', // Considerar validación de formato específico por país más adelante
            'birth_date' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:'.implode(',', array_keys($this->genders)),
            'selectedCountryId' => 'required|exists:countries,id', // Valida que el país seleccionado (para la dirección) exista
            'address_province_id' => 'required|exists:provinces,id',
            'address_city_id' => 'required|exists:cities,id',
            'address_line_1' => 'required|string|max:255',
            'address_postal_code' => 'nullable|string|max:20',
            'invitador_id' => 'required|exists:users,id',
            'patrocinador_id' => 'required|exists:users,id', // Eliminada la regla different:invitador_id
            'terms_accepted' => 'accepted',
            'delivery_method' => 'required|in:pickup,courier',
            'payment_method_selected' => 'required|string|in:'.implode(',', array_keys($this->paymentMethods)),
        ];
    }

    protected array $messages = [
        'first_name.required' => 'Tu nombre es obligatorio.',
        'last_name_paternal.required' => 'Tu apellido paterno es obligatorio.',
        'email.required' => 'Tu correo electrónico es obligatorio.',
        'email.email' => 'El formato del correo electrónico no es válido.',
        'email.unique' => 'Este correo electrónico ya ha sido registrado. Intenta iniciar sesión.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'La confirmación de contraseña no coincide.',
        'dni_ruc.required' => 'Tu DNI/RUC/Identificación es obligatorio.',
        'dni_ruc.unique' => 'Esta identificación ya ha sido registrada.',
        'mobile_phone.required' => 'Tu teléfono móvil es obligatorio.',
        'birth_date.required' => 'Tu fecha de nacimiento es obligatoria.',
        'birth_date.date' => 'El formato de la fecha de nacimiento no es válido.',
        'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser una fecha futura.',
        'gender.required' => 'Por favor, selecciona tu género.',
        'selectedCountryId.required' => 'La selección del país es necesaria para la dirección.',
        'address_province_id.required' => 'Por favor, selecciona tu provincia/estado.',
        'address_city_id.required' => 'Por favor, selecciona tu ciudad.',
        'address_line_1.required' => 'Tu dirección (calle y número) es obligatoria.',
        'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones para continuar.',
        'invitador_id.required' => 'El código o nombre del referidor es obligatorio.',
        'invitador_id.exists' => 'El referidor ingresado no es válido.',
        'patrocinador_id.required' => 'El código o nombre del patrocinador es obligatorio.',
        'patrocinador_id.exists' => 'El patrocinador ingresado no es válido.',
        'patrocinador_id.different' => 'El patrocinador no puede ser la misma persona que el referidor.',
        'delivery_method.required' => 'Debes seleccionar un método de entrega.',
        'payment_method_selected.required' => 'Debes seleccionar un método de pago.',
    ];

    public function updated($propertyName)
    {
        // No validar 'selectedCountryId' aquí directamente porque se maneja por el flujo de modales.
        if ($propertyName === 'password' || $propertyName === 'password_confirmation') {
            $this->validateOnly('password'); // Validar 'password' para que la regla 'confirmed' se active
            // Si solo cambió password_confirmation, también es útil validar solo password_confirmation para otros errores potenciales.
            if ($propertyName === 'password_confirmation') {
                 $this->validateOnly('password_confirmation');
            }
        } elseif ($propertyName !== 'selectedCountryId') {
            $this->validateOnly($propertyName);
        }

        // Estos ya están siendo manejados por sus propios métodos updated*Id
        // if ($propertyName === 'selectedCountryId') {
        //     $this->updatedSelectedCountryId($this->selectedCountryId);
        // }
        // if ($propertyName === 'address_province_id') {
        //     $this->updatedAddressProvinceId($this->address_province_id);
        // }
    }

    // Placeholder para los listeners de UserSearchSelect si se implementan
    public function setInvitadorId(?int $userId = null)
    {
        $this->invitador_id = $userId;
        $this->validateOnly('invitador_id');
    }

    public function setPatrocinadorId(?int $userId = null)
    {
        $this->patrocinador_id = $userId;
        $this->validateOnly('patrocinador_id');
    }

    public function registerAndPlaceOrder()
    {
        $validatedData = $this->validate();
        if ($this->getErrorBag()->isNotEmpty()) {
            Log::debug('registerAndPlaceOrder: Fallaron las validaciones iniciales del formulario.', $this->getErrorBag()->toArray());
            // No es necesario un return aquí, validate() ya lanza ValidationException
        } else {
            Log::debug('registerAndPlaceOrder: Validaciones iniciales del formulario pasadas.', $validatedData);
        }

        // Validación de unicidad para email y dni_ruc
        Log::debug("Verificando unicidad para email: " . $this->email);
        if (\App\Models\User::where('email', $this->email)->exists()) {
            Log::debug("Error de unicidad: Email ya existe - " . $this->email);
            $this->addError('email', 'Este correo electrónico ya ha sido registrado. Intenta iniciar sesión.');
            return;
        }
        Log::debug("Email es único. Verificando DNI: " . $this->dni_ruc);
        if (\App\Models\User::where('dni', $this->dni_ruc)->exists()) {
            Log::debug("Error de unicidad: DNI ya existe - " . $this->dni_ruc);
            $this->addError('dni_ruc', 'Esta identificación ya ha sido registrada.');
            return;
        }
        Log::debug("DNI es único. Validaciones de unicidad pasadas.");

        // Validación de puntos del carrito
        Log::debug("Verificando puntos del carrito. Bundle ID: {$this->selectedActivationBundleId}, Puntos Carrito: {$this->cartTotals['total_points']}");
        if (!$this->selectedActivationBundleId && $this->cartTotals['total_points'] < 20) {
            Log::debug("Error: Puntos insuficientes en el carrito y sin bundle de activación.");
            session()->flash('cart_error', 'Tu pedido debe tener al menos 20 puntos si no seleccionaste un paquete de activación.');
            return;
        }
        Log::debug("Validación de puntos del carrito pasada.");
        
        try {
            $cartService = app(CartService::class);
            DB::transaction(function () use ($validatedData, $cartService) {
                Log::info('Dentro de la transacción DB, antes de crear el usuario.');
                $username = $this->generateUniqueUsername($validatedData['first_name'], $validatedData['last_name_paternal']);
                $fullLastName = trim($validatedData['last_name_paternal'] . ' ' . ($validatedData['last_name_maternal'] ?? ''));

                $user = User::create([
                    'name' => $validatedData['first_name'] . ' ' . $fullLastName, // Concatenar para el campo name
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $fullLastName,
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password']),
                    'username' => $username,
                    'dni' => $validatedData['dni_ruc'],
                    'phone' => $validatedData['mobile_phone'],
                    'birth_date' => $validatedData['birth_date'],
                    'gender' => $validatedData['gender'],
                    'address_country_id' => $validatedData['selectedCountryId'],
                    'address_province_id' => $validatedData['address_province_id'],
                    'address_city_id' => $validatedData['address_city_id'],
                    'address_street' => $validatedData['address_line_1'],
                    'address_postal_code' => $validatedData['address_postal_code'] ?? null,
                    'status' => 'pending_first_payment', // Estado inicial
                    'referrer_id' => $validatedData['invitador_id'],
                    'sponsor_id' => $validatedData['patrocinador_id'], // Ya validado como required
                    'agreed_to_terms_at' => now(), // Marcar aceptación de términos
                ]);
                Log::info("Usuario creado con ID: {$user->id}, Email: {$user->email}");

                // Tarea 2.3.2: Asignar Rol
                // El rol se determinará por si la compra lo califica como socio o si solo es consumidor.
                // Por ahora, asumimos que si hay compra y cumple puntos, es Socio.
                // Esta lógica se puede refinar. Si no hay compra o no cumple puntos, podría ser 'Consumidor Registrado'.
                // Si el `selectedActivationBundleId` está presente, o si `cartTotals['total_points'] >= 20`, es Socio.
                $isSocio = $this->selectedActivationBundleId || ($this->cartTotals['total_points'] >= 20);
                $roleName = $isSocio ? 'Socio Multinivel' : 'Consumidor Registrado';
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->assignRole($role);
                    if ($isSocio) {
                         // Crear billetera si es Socio Multinivel (Fase 3 lo haría al activar, pero podemos asegurar aquí)
                        app(WalletService::class)->ensureWalletExistsForSocio($user);
                    }
                } else {
                    Log::error("Rol '{$roleName}' no encontrado para el usuario {$user->email}.");
                    // Considerar lanzar una excepción o manejar este error de configuración críticamente
                }

                // Tarea 2.3.3: Crear Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => Order::generateOrderNumber(),
                    'status' => 'pending_payment',
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $user->phone,
                    'subtotal' => $this->cartTotals['subtotal_pvs'],      // Mapeado a 'subtotal'
                    'taxes' => $this->cartTotals['vat_amount'],           // Mapeado a 'taxes'
                    'shipping_cost' => $this->cartTotals['shipping_cost'],
                    'discount_amount' => $this->cartTotals['total_discount'],
                    'total' => $this->cartTotals['total_payable'],        // Mapeado a 'total'
                    // 'points_total' se calcula con accesor, no se guarda directamente. El modelo Order no tiene este campo en $fillable.
                    'payment_method' => $this->payment_method_selected,
                    'delivery_method' => $this->delivery_method,
                    // Campos de dirección de envío (pueden ser los mismos del usuario o diferentes si se implementa)
                    'shipping_address_line1' => $user->address_street,
                    'shipping_city_id' => $user->address_city_id,
                    'shipping_province_id' => $user->address_province_id,
                    'shipping_country_id' => $user->address_country_id,
                    'shipping_postal_code' => $user->address_postal_code,
                    'shipping_phone' => $user->phone, // Usar el teléfono del usuario para el envío
                    'billing_address_line1' => $user->address_street, // Asumir igual por ahora
                    'billing_city_id' => $user->address_city_id,
                    'billing_province_id' => $user->address_province_id,
                    'billing_country_id' => $user->address_country_id,
                    'billing_postal_code' => $user->address_postal_code,
                ]);

                // Crear OrderItems
                foreach ($this->cartItems as $cartItem) {
                    $product = Product::find($cartItem['product_id']);
                    if ($product) {
                        Log::debug("Creando OrderItem para producto ID: {$product->id}, Nombre: '{$product->name}', SKU: {$product->sku}");
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $cartItem['quantity'],
                            'unit_price_before_vat' => $cartItem['unit_price_before_vat'],
                            'item_subtotal_before_vat' => $cartItem['item_subtotal_before_vat'], // Corregido
                            'item_vat_amount' => $cartItem['item_total_vat'],          // Corregido y mapeado
                            'item_grand_total' => $cartItem['item_grand_total'],       // Corregido y mapeado
                            'points_value_at_purchase' => $product->points_value,
                            'product_name' => $product->name,
                            'product_sku' => $product->sku,
                            'options' => $cartItem['options'] ?? null, // Añadido por si hay opciones
                            // Asumiendo que CartService no provee 'product_pays_bonus_at_purchase' ni 'product_bonus_amount_at_purchase'
                            // Si fueran necesarios, se deberían obtener del $product o $cartItem
                        ]);
                    }
                }
                
                // TODO: Tarea 2.5 - Manejo del Flujo de Pago Específico
                // TODO: Tarea 2.6 - Notificaciones por Email (Pagos Offline)
                
                // Tarea 2.7 (parcial) - Limpiar carrito
                $cartService->clear();
                $this->refreshCartData($cartService); // Actualizar UI del carrito (debería estar vacío)

                Log::info("Usuario {$user->email} y Pedido {$order->order_number} creados exitosamente. Pendiente de pago.");
                session()->flash('registration_success', '¡Registro y pedido creados! Sigue las instrucciones para el pago.');

                // TODO: Tarea 2.7 (parcial) - Resetear formulario (se hará después de la redirección o mensaje de pago)
                // $this->resetFormFields(); // Método a crear para limpiar el formulario

                // Aquí vendría la redirección o mensaje según el método de pago
                if ($this->payment_method_selected === 'online_credit_card') {
                    // Tarea 4.2.2: Lógica de redirección a pasarela
                    Log::info("Pago online seleccionado para Pedido {$order->order_number}. Preparando para redirigir.");
                    
                    // Placeholder: Simular la obtención de una URL de pago y redirigir.
                    // En una implementación real, aquí se interactuaría con el servicio de la pasarela.
                    // $paymentGatewayService = app(YourPaymentGatewayService::class);
                    // $redirectUrl = $paymentGatewayService->initiatePayment($order, route('payment.callback'));
                    
                    // Simulación de redirección:
                    // $this->redirect($redirectUrl);
                    // Por ahora, para no romper el flujo sin una pasarela real:
                    session()->flash('registration_success', "¡Registro y pedido #{$order->order_number} creados! Normalmente serías redirigido para el pago online. Esta función está en desarrollo.");
                    $this->resetFormFields(); // Resetear después de la "redirección" simulada

                } else { // Pagos Offline
                    Log::info("Pago offline ({$this->payment_method_selected}) seleccionado para Pedido {$order->order_number}. Enviando correos...");
                    
                    Mail::to($user->email)->send(new WelcomePendingPaymentMail($user, $order));
                    Mail::to($user->email)->send(new OrderPlacedOfflinePaymentMail($user, $order, $this->paymentMethods[$this->payment_method_selected]));
                    
                    session()->flash('registration_success', "¡Registro y pedido #{$order->order_number} creados! Revisa tu correo para los detalles e instrucciones de pago ({$this->paymentMethods[$this->payment_method_selected]}).");
                    $this->resetFormFields();
                }

            }); // Fin de DB::transaction

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Las validaciones de Livewire ya muestran los errores.
            // Podemos loguear si es necesario.
            Log::warning('ValidationException en registerAndPlaceOrder: ' . $e->getMessage(), $e->errors());
            throw $e; // Re-lanzar para que Livewire maneje los errores de validación
        } catch (\Exception $e) {
            Log::error('Error general en registerAndPlaceOrder: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            session()->flash('registration_error', 'Ocurrió un error inesperado al procesar tu registro y pedido. Por favor, inténtalo de nuevo o contacta a soporte.');
        }
    }

    protected function resetFormFields()
    {
        // Guarda el país seleccionado para no resetearlo, ya que es parte del contexto de la página.
        $currentSelectedCountryId = $this->selectedCountryId;

        $this->reset([
            'first_name', 'last_name_paternal', 'last_name_maternal', 'email',
            'password', 'password_confirmation', 'dni_ruc', 'mobile_phone',
            'birth_date', 'gender', 'address_province_id', 'address_city_id',
            'address_line_1', 'address_postal_code', 'invitador_id', 'patrocinador_id',
            'terms_accepted', 'delivery_method', 'payment_method_selected',
            'selectedActivationBundleId'
        ]);
        
        // Restaurar el país seleccionado y recargar provincias
        $this->selectedCountryId = $currentSelectedCountryId;
        if ($this->selectedCountryId) {
            $this->updatedSelectedCountryId($this->selectedCountryId); // Esto limpiará provincias y ciudades
        } else {
            // Si por alguna razón no hay país, limpiar explícitamente
            $this->provinces = collect();
            $this->cities = collect();
        }


        // Los datos del carrito ya se limpiaron dentro de la transacción.
        // Forzar el estado inicial de los modales por si el usuario quiere hacer otro registro.
        // $this->showCountryModal = true; // Opcional: decidir si se quiere forzar el modal de país de nuevo.
        // $this->showActivationBundleModal = false;
        // $this->availableActivationBundles = collect();
        // $this->catalogProducts = collect();
        // $this->cartItems = collect();
        // $this->cartTotals = $this->getDefaultCartTotals();
        // $this->refreshCartData(app(CartService::class)); // Para asegurar que el carrito en UI esté vacío.
    }
    
    // Método para generar username único (adaptado de UserRegistrationForm)
    protected function generateUniqueUsername(string $firstName, string $lastNameP): string
    {
        $baseUsername = Str::slug($firstName . substr($lastNameP, 0, 1), '');
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


    public function updateQuantity(string $cartItemId, int $quantity, CartService $cartService)
    {
        if ($quantity < 1) {
            $this->removeFromCart($cartItemId, $cartService);
            return;
        }
        $cartService->update($cartItemId, $quantity);
        $this->refreshCartData($cartService);
        session()->flash('cart_message', 'Cantidad actualizada.');
    }

    public function removeFromCart(string $cartItemId, CartService $cartService)
    {
        $cartService->remove($cartItemId);
        $this->refreshCartData($cartService);
        session()->flash('cart_message', 'Ítem eliminado del carrito.');
    }
}
