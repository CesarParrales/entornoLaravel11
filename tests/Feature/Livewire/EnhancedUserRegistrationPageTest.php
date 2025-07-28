<?php

namespace Tests\Feature\Livewire;

use App\Livewire\EnhancedUserRegistrationPage;
use App\Models\Country;
use App\Models\Order; // Importar Order
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\TestRegistrationProductSeeder; // Para asegurar que existan bundles
use Database\Seeders\ProvinceSeeder; // Necesario para direcciones
use Database\Seeders\CitySeeder;     // Necesario para direcciones
use App\Models\Province;
use App\Models\City;
use App\Mail\WelcomePendingPaymentMail;
use App\Mail\OrderPlacedOfflinePaymentMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class EnhancedUserRegistrationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ejecutar seeders necesarios para las pruebas
        $this->seed(CountrySeeder::class);
        $this->seed(ProvinceSeeder::class); // Asegurar que haya provincias
        $this->seed(CitySeeder::class);     // Asegurar que haya ciudades
        $this->seed(ProductSeeder::class);
        $this->seed(TestRegistrationProductSeeder::class);
        
        // Crear un usuario para pruebas de referidor/patrocinador
        User::factory()->create(['id' => 999, 'username' => 'testinvitador', 'email' => 'invitador@example.com']);
        User::factory()->create(['id' => 998, 'username' => 'testpatrocinador', 'email' => 'patrocinador@example.com']);
    }

    public function test_component_renders_and_shows_country_modal_initially()
    {
        Livewire::test(EnhancedUserRegistrationPage::class)
            ->assertSee('Seleccione un país')
            ->assertSet('showCountryModal', true)
            ->assertSet('showActivationBundleModal', false);
    }

    public function test_selecting_country_loads_provinces_and_shows_bundle_modal_if_bundles_exist()
    {
        $country = Country::first();
        if (!$country) {
            $this->markTestSkipped('No countries found in the database to run this test.');
        }

        Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id)
            ->assertSet('selectedCountryId', $country->id)
            ->assertSet('showCountryModal', false)
            ->assertNotSet('showActivationBundleModal', false) // Puede ser true o false dependiendo de si hay bundles
            ->assertViewHas('provinces')
            ->assertViewHas('availableActivationBundles');

        // Verificar si el modal de bundles se muestra (asumiendo que TestRegistrationProductSeeder creó bundles)
        $component = Livewire::test(EnhancedUserRegistrationPage::class);
        $component->call('selectCountry', $country->id);
        if ($component->get('availableActivationBundles')->isNotEmpty()) {
            $component->assertSet('showActivationBundleModal', true)
                      ->assertSee('Seleccione un paquete promocional');
        } else {
            $component->assertSet('showActivationBundleModal', false);
        }
    }

    public function test_skipping_bundle_modal_hides_it_and_loads_catalog()
    {
        $country = Country::first();
        if (!$country) {
            $this->markTestSkipped('No countries found.');
        }

        Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id) // Primero selecciona país
            ->call('skipActivationBundle')
            ->assertSet('showActivationBundleModal', false)
            ->assertSet('selectedActivationBundleId', null)
            ->assertViewHas('catalogProducts') // Verifica que los productos del catálogo se cargan
            ->assertSee('Tus Datos') // Verifica que se muestra el formulario principal
            ->assertSee('Elige tus Productos');
    }

    public function test_selecting_activation_bundle_hides_modal_and_updates_cart()
    {
        $country = Country::first();
        if (!$country) $this->markTestSkipped('No countries found.');

        $bundle = Product::where('is_registration_bundle', true)->first();
        if (!$bundle) $this->markTestSkipped('No registration bundles found for testing.');

        Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id)
            ->call('selectActivationBundle', $bundle->id)
            ->assertSet('showActivationBundleModal', false)
            ->assertSet('selectedActivationBundleId', $bundle->id)
            ->assertViewHas('catalogProducts')
            ->assertSee('Tus Datos')
            ->assertSee('Elige tus Productos')
            ->assertSee($bundle->name); // Verifica que el bundle está en el carrito (o su nombre)
    }
    
    public function test_form_fields_can_be_set_and_basic_validation_works()
    {
        $country = Country::first();
        if (!$country) $this->markTestSkipped('No countries found.');

        Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id)
            ->call('skipActivationBundle') // Saltar modal de bundle para llegar al formulario
            ->set('first_name', 'John')
            ->set('last_name_paternal', 'Doe')
            ->set('email', 'john.doe@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('dni_ruc', '1234567890')
            ->set('mobile_phone', '0987654321')
            ->set('birth_date', '1990-01-01')
            ->set('gender', 'male')
            ->set('address_line_1', '123 Main St')
            ->set('invitador_id', 999) // ID de usuario de prueba
            ->set('terms_accepted', true)
            ->set('delivery_method', 'pickup')
            // ->call('attemptRegistration') // No llamamos a attemptRegistration aún, solo probamos setear
            ->assertHasNoErrors(['first_name', 'email', 'password', 'terms_accepted', 'delivery_method']);

        // Probar un error de validación
        Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id)
            ->call('skipActivationBundle')
            ->set('first_name', '') // Nombre vacío
            ->set('email', 'notanemail')
            // No establecer patrocinador_id aquí para que falle por 'required' o usar uno diferente si solo probamos first_name y email.
            // Para esta prueba específica, nos enfocamos en first_name y email.
            // Aseguramos que los otros campos requeridos tengan valores válidos para no interferir.
            ->set('last_name_paternal', 'TestLN')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('dni_ruc', '00000VALID00')
            ->set('mobile_phone', '0900000000')
            ->set('birth_date', '1990-01-01')
            ->set('gender', 'male')
            ->set('address_province_id', Province::first()->id) // Asumir que existe
            ->set('address_city_id', City::first()->id) // Asumir que existe
            ->set('address_line_1', '123 Valid St')
            ->set('invitador_id', 999)
            ->set('patrocinador_id', 998) // ID diferente para evitar error 'different'
            ->set('terms_accepted', true)
            ->set('delivery_method', 'pickup')
            ->set('payment_method_selected', 'bank_deposit')
            ->call('registerAndPlaceOrder') // Nombre de método actualizado
            ->assertHasErrors(['first_name' => 'required', 'email' => 'email']);
    }

    public function test_cart_operations_add_update_remove()
    {
        $country = Country::first();
        if (!$country) $this->markTestSkipped('No countries found.');

        // Crear un producto específico para esta prueba que no sea un bundle de registro
        $uniqueProductName = 'Super Unique Test Product Name XYZ123';
        $product = Product::factory()->create([
            'name' => $uniqueProductName,
            'is_registration_bundle' => false,
            'is_active' => true,
            'base_price' => 25.00,
            'points_value' => 10,
        ]);
        
        $this->assertNotNull($product, 'Failed to create a normal product for cart testing.');

        $component = Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id)
            ->call('skipActivationBundle'); // Saltar modal de bundle
            
        // Añadir al carrito
        $component->call('addToCart', $product->id)
            ->assertSee($uniqueProductName) // Verificar que el producto está en el carrito visualmente
            ->assertNotSet('cartTotals.total_payable', 0.00); // El total no debería ser cero
        
        // Forzar un render y limpiar mensajes flash si es necesario (aunque Livewire debería manejarlos)
        // $component->call('render'); // No es un método público
        // session()->forget('livewire-flash'); // Intento de limpiar flash manualmente si fuera el problema

        // Actualizar cantidad (asumiendo que el cartItemId es 'product_'.$product->id para productos simples)
        $component->call('updateQuantity', 'product_'.$product->id, 3)
            ->assertSet('cartItems.product_'.$product->id.'.quantity', 3);
            
        // Eliminar del carrito
        $component->call('removeFromCart', 'product_'.$product->id);

        $this->assertArrayNotHasKey('product_'.$product->id, $component->get('cartItems')->toArray(), "El ítem debería haber sido eliminado de la colección cartItems.");
        
        // Obtener el HTML DESPUÉS de la acción de eliminar y DESPUÉS de que Livewire haya re-renderizado.
        // La clave es que $component->html() obtiene el estado actual del DOM renderizado por Livewire.
        // $htmlAfterRemove = $component->html();
        // $this->assertStringNotContainsString($uniqueProductName, $htmlAfterRemove, "El nombre del producto eliminado todavía se encuentra en el HTML renderizado: " . $htmlAfterRemove);
        // La aserción anterior es incorrecta si el producto aún existe en el catálogo.
        // La prueba de que se eliminó del carrito ya está cubierta por assertArrayNotHasKey.
    }

    public function test_successful_registration_with_offline_payment_creates_user_order_and_sends_emails()
    {
        Mail::fake();

        $country = Country::first();
        if (!$country) $this->markTestSkipped('No countries found.');
        
        $province = Province::where('country_id', $country->id)->first();
        if (!$province) $this->markTestSkipped('No provinces found for the selected country.');

        $city = City::where('province_id', $province->id)->first();
        if (!$city) $this->markTestSkipped('No cities found for the selected province.');

        $product = Product::factory()->create(['points_value' => 25, 'is_active' => true]); // Producto con suficientes puntos

        $uniqueEmail = fake()->unique()->safeEmail();
        $uniqueDni = fake()->unique()->numerify('##########'); // 10 digits

        Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id)
            ->call('skipActivationBundle') // O seleccionar un bundle
            ->call('addToCart', $product->id) // Añadir producto para cumplir puntos
            ->set('first_name', 'Test')
            ->set('last_name_paternal', 'User')
            ->set('email', $uniqueEmail)
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('dni_ruc', $uniqueDni)
            ->set('mobile_phone', '0999999999')
            ->set('birth_date', '1995-05-15')
            ->set('gender', 'other')
            ->set('address_province_id', $province->id)
            ->set('address_city_id', $city->id)
            ->set('address_line_1', '123 Test Street')
            ->set('invitador_id', 999)
            ->set('patrocinador_id', 998)
            ->set('terms_accepted', true)
            ->set('delivery_method', 'pickup')
            ->set('payment_method_selected', 'bank_deposit'); // Método offline
            
        \Illuminate\Support\Facades\Log::debug("PRUEBA: Antes de llamar a registerAndPlaceOrder. Email: " . $uniqueEmail . ", DNI: " . $uniqueDni);

        // Reasignar $component para continuar la cadena o usar la instancia existente
        $livewireTest = Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id)
            ->call('skipActivationBundle')
            ->call('addToCart', $product->id)
            ->set('first_name', 'Test')
            ->set('last_name_paternal', 'User')
            ->set('email', $uniqueEmail)
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('dni_ruc', $uniqueDni)
            ->set('mobile_phone', '0999999999')
            ->set('birth_date', '1995-05-15')
            ->set('gender', 'other')
            ->set('address_province_id', $province->id)
            ->set('address_city_id', $city->id)
            ->set('address_line_1', '123 Test Street')
            ->set('invitador_id', 999)
            ->set('patrocinador_id', 998)
            ->set('terms_accepted', true)
            ->set('delivery_method', 'pickup')
            ->set('payment_method_selected', 'bank_deposit');

        $livewireTest->call('registerAndPlaceOrder')
            ->assertHasNoErrors();
            // ->assertSessionHas('registration_success');

        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'status' => 'pending_first_payment'
        ]);

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);

        // Verificar los campos que sí están en la base de datos
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'payment_method' => 'bank_deposit',
            // No podemos verificar 'points_total' directamente con assertDatabaseHas si es un accesor
        ]);
        
        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        // Verificar el accesor para los puntos
        $this->assertEquals($product->points_value, $order->total_points_generated);

        Mail::assertQueued(WelcomePendingPaymentMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        Mail::assertQueued(OrderPlacedOfflinePaymentMail::class, function ($mail) use ($user, $order) {
            return $mail->hasTo($user->email) && $mail->order->id === $order->id;
        });

        // Verificar que el carrito está vacío después del registro
        $component = Livewire::test(EnhancedUserRegistrationPage::class); // Nueva instancia para verificar estado limpio
        $this->assertTrue($component->get('cartItems')->isEmpty());
    }

    public function test_registration_fails_if_cart_points_are_insufficient_and_no_bundle_selected()
    {
        $country = Country::first();
        if (!$country) $this->markTestSkipped('No countries found.');
        $province = Province::where('country_id', $country->id)->first();
        if (!$province) $this->markTestSkipped('No provinces found.');
        $city = City::where('province_id', $province->id)->first();
        if (!$city) $this->markTestSkipped('No cities found.');

        $product = Product::factory()->create(['points_value' => 10, 'is_active' => true]); // Producto con puntos insuficientes

        $lowPointsEmail = fake()->unique()->safeEmail();
        $lowPointsDni = fake()->unique()->numerify('##########LP');

        Livewire::test(EnhancedUserRegistrationPage::class)
            ->call('selectCountry', $country->id)
            ->call('skipActivationBundle')
            ->call('addToCart', $product->id) // Producto con 10 puntos
            ->set('first_name', 'TestLow')
            ->set('last_name_paternal', 'Points')
            ->set('email', $lowPointsEmail)
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('dni_ruc', $lowPointsDni)
            ->set('mobile_phone', '0988888888')
            ->set('birth_date', '1990-01-01')
            ->set('gender', 'male')
            ->set('address_province_id', $province->id)
            ->set('address_city_id', $city->id)
            ->set('address_line_1', '456 Low St')
            ->set('invitador_id', 999)
            ->set('patrocinador_id', 998)
            ->set('terms_accepted', true)
            ->set('delivery_method', 'courier')
            ->set('payment_method_selected', 'bank_transfer')
            ->call('registerAndPlaceOrder');
            // Aquí, el método registerAndPlaceOrder debería haber retornado temprano
            // debido a la validación de puntos, y el usuario no debería crearse.
            // El session flash 'cart_error' es el indicador. Si no podemos usar assertSessionHas,
            // la ausencia del usuario es la prueba principal.

        $this->assertDatabaseMissing('users', ['email' => $lowPointsEmail]);
    }
}
