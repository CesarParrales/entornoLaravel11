<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestRegistrationProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurarse de que exista al menos una categoría
        $category = Category::first();
        if (!$category) {
            $category = Category::factory()->create(['name' => 'Categoría General', 'slug' => Str::slug('Categoría General')]);
        }

        Product::create([
            'name' => 'Bundle de Activación de Prueba',
            'slug' => Str::slug('Bundle de Activación de Prueba'),
            'sku' => 'BUNDLE-TEST-001',
            'description' => 'Este es un bundle de activación de prueba para verificar la funcionalidad.',
            'short_description' => 'Bundle de prueba.',
            'product_type' => 'bundle', // o 'simple' según la lógica de bundles
            'base_price' => 100.00,
            'points_value' => 50,
            'is_active' => true,
            'is_featured' => false,
            'category_id' => $category->id,
            'is_registration_bundle' => true,
            'registration_bundle_price' => 90.00,
            'pays_bonus' => false, // Asumiendo que el bundle en sí no paga bono directo
        ]);

        $this->command->info('Producto de bundle de activación de prueba creado.');
    }
}
