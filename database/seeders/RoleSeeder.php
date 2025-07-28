<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Roles
        $roles = [
            // Usuarios Administrativos
            'Administrador Global',
            'Gerente',
            'Contador',
            'Jefe de Bodega',
            'Bodeguero',
            'Operador POS',
            'Webmaster/Soporte Técnico',
            // Usuarios de Desarrollo
            'SuperDev',
            'Dev',
            // Usuarios de la Plataforma
            'Socio Multinivel',
            'Consumidor Registrado',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $this->command->info('Default roles seeded.');

        // Assign all permissions to SuperDev and Administrador Global
        $superDevRole = Role::findByName('SuperDev');
        $adminGlobalRole = Role::findByName('Administrador Global');
        $allPermissions = Permission::all();

        if ($superDevRole) {
            $superDevRole->syncPermissions($allPermissions);
            $this->command->info('All permissions assigned to SuperDev role.');
        }

        if ($adminGlobalRole) {
            $adminGlobalRole->syncPermissions($allPermissions);
            $this->command->info('All permissions assigned to Administrador Global role.');
        }

        // Example: Assign specific permissions to Socio Multinivel
        $socioRole = Role::findByName('Socio Multinivel');
        if ($socioRole) {
            $socioPermissions = [
                'socio:view_dashboard', 'socio:view_profile', 'socio:update_profile', 
                'socio:view_downline', 'socio:view_commissions', 'socio:view_rank', 
                'socio:place_orders', 'socio:generate_referral_link',
                'product:view_any', // Para ver el catálogo
                'product:view',
            ];
            foreach ($socioPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $socioRole->givePermissionTo($permission);
                }
            }
            $this->command->info('Basic permissions assigned to Socio Multinivel role.');
        }
         // Example: Assign specific permissions to Consumidor Registrado
        $consumidorRole = Role::findByName('Consumidor Registrado');
        if ($consumidorRole) {
            $consumidorPermissions = [
                'customer:view_profile', 'customer:update_profile', 
                'customer:view_orders', 'customer:place_orders',
                'product:view_any', // Para ver el catálogo
                'product:view',
            ];
            foreach ($consumidorPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $consumidorRole->givePermissionTo($permission);
                }
            }
            $this->command->info('Basic permissions assigned to Consumidor Registrado role.');
        }


        // TODO: Assign specific, granular permissions to other roles:
        // Gerente, Contador, Jefe de Bodega, Bodeguero, Operador POS, Webmaster/Soporte Técnico, Dev
        // This will require careful consideration of what each role should be able to do.
        // For now, they are created but have no specific permissions beyond default.
    }
}
