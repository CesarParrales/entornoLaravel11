<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            CountrySeeder::class,
            ProvinceSeeder::class, // Added
            CitySeeder::class,     // Added
            CategorySeeder::class, // Añadido para categorías base
            RankSeeder::class, // Añadido para rangos base
            BonusTypeSeeder::class, // Añadido para tipos de bono base
            FinancialFreedomCommissionTierSeeder::class, // Añadido para niveles de comisión
            MobilizationBonusTierSeeder::class, // Añadido para niveles de bono movilización
            RecognitionBonusTierSeeder::class, // Añadido para niveles de bono reconocimiento
            ProductSeeder::class, // Añadido para productos base
            AdminUserSeeder::class, // Añadido para crear el usuario administrador
            SocioZeroSeeder::class,
            TestRegistrationProductSeeder::class, // Added for testing registration bundle product
            // Aquí puedes añadir otros seeders.
            // OrderSeeder::class, // Ejemplo
        ]);

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
