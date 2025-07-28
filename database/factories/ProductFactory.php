<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true); // Genera 3 palabras únicas para el nombre
        return [
            'name' => ucwords($name),
            'slug' => \Illuminate\Support\Str::slug($name),
            'sku' => $this->faker->unique()->ean8(),
            'description' => $this->faker->paragraph,
            'short_description' => $this->faker->sentence,
            'product_type' => 'simple', // 'simple', 'bundle_fixed', 'bundle_configurable'
            'base_price' => $this->faker->randomFloat(2, 10, 200),
            'points_value' => $this->faker->numberBetween(5, 50),
            'is_active' => true,
            'category_id' => \App\Models\Category::factory(), // Asocia a una categoría creada por factory
            'is_registration_bundle' => false, // Por defecto no es un bundle de registro
            'registration_bundle_price' => null,
            // Añadir otros campos según sea necesario para la validez del modelo
        ];
    }
}
