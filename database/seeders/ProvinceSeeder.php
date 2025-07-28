<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = \App\Models\Country::where('is_active', true)->get();

        if ($countries->isEmpty()) {
            $this->command->warn('No active countries found. Skipping ProvinceSeeder.');
            return;
        }

        foreach ($countries as $country) {
            // Ejemplo para Ecuador (EC)
            if ($country->iso_code_2 === 'EC') {
                \App\Models\Province::updateOrCreate(
                    ['country_id' => $country->id, 'name' => 'Pichincha'],
                    ['is_active' => true]
                );
                \App\Models\Province::updateOrCreate(
                    ['country_id' => $country->id, 'name' => 'Guayas'],
                    ['is_active' => true]
                );
                \App\Models\Province::updateOrCreate(
                    ['country_id' => $country->id, 'name' => 'Azuay'],
                    ['is_active' => true]
                );
            }
            // Añadir más provincias para otros países si es necesario para las pruebas
        }
        $this->command->info('Provinces seeded successfully.');
    }
}
