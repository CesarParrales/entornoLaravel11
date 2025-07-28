<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Country;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SocioZeroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolSocioMultinivel = 'Socio Multinivel';
        $nombrePais = 'Ecuador';
        $usernameSocioZero = 'apaysami_principal';
        $emailSocioZero = 'principal@apaysami.com';
        
        $socioZeroFirstName = 'Apaysami';
        $socioZeroLastName = 'Principal';

        // 1. Buscar el país
        $country = Country::where('name', $nombrePais)->first();
        if (!$country) {
            Log::error("País '{$nombrePais}' no encontrado. El Socio 0 no pudo ser creado.");
            $this->command->error("País '{$nombrePais}' no encontrado. Asegúrate de que exista o ejecuta el CountrySeeder.");
            return;
        }

        // 2. Buscar el rol
        $role = Role::where('name', $rolSocioMultinivel)->first();
        if (!$role) {
            Log::error("Rol '{$rolSocioMultinivel}' no encontrado. El Socio 0 no pudo ser creado.");
            $this->command->error("Rol '{$rolSocioMultinivel}' no encontrado. Asegúrate de que exista o ejecuta el RoleSeeder/PermissionSeeder.");
            return;
        }

        // 3. Crear o actualizar el Socio 0
        $socioZero = User::updateOrCreate(
            ['username' => $usernameSocioZero],
            [
                'name' => $socioZeroFirstName . ' ' . $socioZeroLastName, // Campo 'name'
                'first_name' => $socioZeroFirstName,
                'last_name' => $socioZeroLastName,
                'email' => $emailSocioZero,
                'password' => Hash::make('password'), // Cambiar esto en producción
                'dni' => '1792662591001', // Corregido de dni_ruc
                'status' => 'active',
                'email_verified_at' => Carbon::now(),
                // 'activated_at' => Carbon::now(), // Considerar si se mantiene o se basa en status/email_verified_at
                
                // Campos de perfil adicionales
                'phone' => '0999999999', // Default phone
                'birth_date' => Carbon::now()->subYears(25)->format('Y-m-d'), // Default birth_date
                'gender' => 'other', // Default gender
                'agreed_to_terms' => true,
                'profile_completed' => true,

                // Dirección
                'address_country_id' => $country->id, // Corregido de country_id
                'address_state' => 'Pichincha',      // Corregido de province
                'address_city' => 'Quito',           // Corregido de city
                'address_street' => 'Calle Padre José Carolo Oe1-238 y Río Coca', // Corregido de address_line_1
                'address_postal_code' => '170101', // Default postal code

                // MLM Fields
                'sponsor_id' => null,
                'referrer_id' => null, // Corregido de invitador_id
                'placement_id' => null, // Socio 0 es raíz
                'mlm_level' => 0, // Socio 0 es nivel 0
            ]
        );

        // 4. Asignar el rol
        if ($socioZero->hasRole($rolSocioMultinivel)) {
            $this->command->info("Socio 0 '{$socioZero->name}' ya tiene el rol '{$rolSocioMultinivel}'.");
        } else {
            $socioZero->assignRole($role);
            $this->command->info("Rol '{$rolSocioMultinivel}' asignado al Socio 0 '{$socioZero->name}'.");
        }

        $this->command->info("Socio 0 '{$socioZero->name}' creado/actualizado exitosamente con email '{$socioZero->email}' y username '{$socioZero->username}'. ¡Recuerda cambiar la contraseña por defecto ('password')!");
    }
}
