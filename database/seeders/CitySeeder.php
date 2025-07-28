<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provincesData = [
            'Pichincha' => ['Quito', 'Sangolquí', 'Machachi'],
            'Guayas' => ['Guayaquil', 'Durán', 'Samborondón'],
            'Azuay' => ['Cuenca', 'Gualaceo', 'Paute'],
        ];

        foreach ($provincesData as $provinceName => $cities) {
            $province = \App\Models\Province::where('name', $provinceName)->first();
            if ($province) {
                foreach ($cities as $cityName) {
                    \App\Models\City::updateOrCreate(
                        ['province_id' => $province->id, 'name' => $cityName],
                        [
                            'country_id' => $province->country_id, // Añadir country_id desde la provincia
                            'is_active' => true
                        ]
                    );
                }
            } else {
                $this->command->warn("Province '{$provinceName}' not found. Skipping cities for this province.");
            }
        }
        $this->command->info('Cities seeded successfully.');
    }
}
