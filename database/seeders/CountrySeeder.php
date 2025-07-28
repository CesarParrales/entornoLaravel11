<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::updateOrCreate(
            ['iso_code_2' => 'EC'], // Clave para buscar/actualizar
            [
                'name' => 'Ecuador',
                'iso_code_3' => 'ECU',
                // 'geoname_id' => 3658394, // Removed as the column was removed
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'is_active' => true,
                'phone_country_code' => '+593',
                'dni_label' => 'Cédula/RUC',
                // 'dni_format_regex' => null, // Ejemplo: /^[0-9]{10}$|^[0-9]{13}$/
                // 'dni_fixed_length' => null, // Ejemplo: 10 o 13 (podría ser más complejo)
                // 'phone_national_format_regex' => null, // Ejemplo: /^09[0-9]{8}$|^0[2-7][0-9]{7}$/
                // 'phone_national_fixed_length' => null, // Ejemplo: 9 para móviles, 7 para fijos (sin prefijo de ciudad)
                'vat_rate' => 0.15, // 15%
                'vat_label' => 'IVA',
                'default_language_code' => 'es',
                'default_timezone' => 'America/Guayaquil',
                'administrative_division_label_1' => 'Provincia',
                'administrative_division_label_2' => 'Cantón', // O Ciudad, según se prefiera
                'administrative_division_label_3' => 'Parroquia',
                // usd_exchange_rate se puede dejar null si la moneda es USD o se actualiza de otra forma
            ]
        );

        // Añadir otros países si se desea
        // Ejemplo:
        // Country::updateOrCreate(
        //     ['iso_code_2' => 'CO'],
        //     [
        //         'name' => 'Colombia',
        //         'iso_code_3' => 'COL',
        //         'geoname_id' => 3686110, // GeoNames ID for Colombia
        //         'currency_code' => 'COP',
        //         'currency_symbol' => '$',
        //         'is_active' => true,
        //         'phone_country_code' => '+57',
        //         'dni_label' => 'Cédula de Ciudadanía',
        //         'vat_rate' => 0.19,
        //         'vat_label' => 'IVA',
        //         'default_language_code' => 'es',
        //         'default_timezone' => 'America/Bogota',
        //         'administrative_division_label_1' => 'Departamento',
        //         'administrative_division_label_2' => 'Municipio',
        //     ]
        // );

        $this->command->info('Países base (Ecuador) creados/actualizados.');
    }
}
