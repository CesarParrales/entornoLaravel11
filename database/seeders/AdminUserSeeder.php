<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminFirstName = 'Super';
        $adminLastName = 'Admin';
        $adminEmail = 'admin@apaysami.com';
        $adminUsername = 'superadmin';
        $adminPassword = 'password'; // Cambiar en producción

        // Verificar si el rol SuperDev existe
        $superDevRole = Role::where('name', 'SuperDev')->first();

        if (!$superDevRole) {
            $this->command->error("El rol 'SuperDev' no fue encontrado. Ejecute PermissionSeeder y RoleSeeder primero.");
            return;
        }
        
        $defaultCountry = Country::where('iso_code_2', 'EC')->first() ?? Country::first();
        if (!$defaultCountry) {
            $this->command->warn("No se encontraron países en la base de datos. El usuario admin se creará sin país por defecto si es posible, o puede fallar si el campo es requerido sin un default en la DB.");
            // Considerar si la creación debe detenerse si no hay país.
            // Por ahora, se intentará con null si $defaultCountry es null.
        }

        // Crear el usuario administrador si no existe
        $adminUser = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminFirstName . ' ' . $adminLastName, // Añadido el campo 'name'
                'first_name' => $adminFirstName,
                'last_name' => $adminLastName,
                'username' => $adminUsername,
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
                'status' => 'active', 
                'agreed_to_terms' => true, 
                'profile_completed' => true, 
                'birth_date' => now()->subYears(30)->format('Y-m-d'), 
                'gender' => 'other', 
                'phone' => '0000000000', 
                'dni' => '0000000000', 
                'address_street' => 'N/A',
                'address_city' => 'N/A',
                'address_state' => 'N/A',
                'address_postal_code' => 'N/A',
                'address_country_id' => $defaultCountry ? $defaultCountry->id : null,
            ]
        );

        // Asignar el rol SuperDev al usuario administrador
        if (!$adminUser->hasRole('SuperDev')) {
            $adminUser->assignRole($superDevRole);
            $this->command->info("Rol 'SuperDev' asignado al usuario {$adminEmail}.");
        } else {
            $this->command->info("Usuario {$adminEmail} ya tiene el rol 'SuperDev'.");
        }

        $this->command->info("Usuario administrador '{$adminEmail}' (username: '{$adminUsername}') verificado/creado. Contraseña por defecto: '{$adminPassword}'. ¡Por favor, cámbiela después del primer inicio de sesión!");
    }
}