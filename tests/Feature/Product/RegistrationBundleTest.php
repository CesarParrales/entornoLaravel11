<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use Database\Seeders\TestRegistrationProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker; // No se usará Faker directamente aquí
use Tests\TestCase;

class RegistrationBundleTest extends TestCase
{
    use RefreshDatabase; // Para asegurar una BD limpia en cada test

    /**
     * Prueba que el producto de bundle de activación se crea correctamente
     * y tiene los valores esperados para los nuevos campos.
     */
    public function test_registration_bundle_product_is_created_correctly(): void
    {
        // Ejecutar el seeder específico que crea nuestro producto de prueba
        $this->seed(TestRegistrationProductSeeder::class);

        // Buscar el producto de prueba por su SKU o nombre
        $testProduct = Product::where('sku', 'BUNDLE-TEST-001')->first();

        // Verificar que el producto existe
        $this->assertNotNull($testProduct, "El producto de bundle de activación de prueba no fue encontrado.");

        // Verificar los valores de los nuevos campos
        $this->assertTrue($testProduct->is_registration_bundle, "El campo 'is_registration_bundle' debería ser true.");
        $this->assertEquals(90.00, $testProduct->registration_bundle_price, "El campo 'registration_bundle_price' no tiene el valor esperado.");

        // Opcionalmente, verificar otros campos importantes si es necesario
        $this->assertEquals('Bundle de Activación de Prueba', $testProduct->name);
        $this->assertEquals(100.00, $testProduct->base_price);
        $this->assertEquals(50, $testProduct->points_value);
        $this->assertTrue($testProduct->is_active);
    }
}
