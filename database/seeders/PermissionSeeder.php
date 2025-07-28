<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role; // Aunque no lo usemos aquí directamente, es bueno tenerlo por si acaso.
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Gestión de Usuarios
            'user:view_any', 'user:view', 'user:create', 'user:update', 'user:delete', 'user:assign_roles', 'user:impersonate',
            // Gestión de Roles
            'role:view_any', 'role:view', 'role:create', 'role:update', 'role:delete', 'role:assign_permissions',
            // Gestión de Permisos
            'permission:view_any', 'permission:view', 'permission:create', 'permission:update', 'permission:delete',
            // Gestión de Productos
            'product:view_any', 'product:view', 'product:create', 'product:update', 'product:delete', 'product:manage_categories', 'product:manage_attributes', 'product:manage_points',
            // Gestión de Categorías
            'category:view_any', 'category:view', 'category:create', 'category:update', 'category:delete',
            // Gestión de Órdenes
            'order:view_any', 'order:view', 'order:update_status', 'order:manage_refunds',
            // Gestión de Red Multinivel
            'mlm_network:view_tree', 'mlm_network:manage_sponsorship', 'mlm_network:view_member_details',
            // Gestión de Planes de Compensación
            'compensation_plan:view_any', 'compensation_plan:view', 'compensation_plan:create', 'compensation_plan:update', 'compensation_plan:delete', 'compensation_plan:manage_bonus_rules',
            // Gestión de Rangos
            'rank:view_any', 'rank:view', 'rank:create', 'rank:update', 'rank:delete', 'rank:manual_assignment',
            // Gestión de Bodegas
            'warehouse:view_any', 'warehouse:view', 'warehouse:create', 'warehouse:update', 'warehouse:delete', 'warehouse:manage_inventory', 'warehouse:manage_transfers',
            // Gestión de Puntos de Venta (POS)
            'pos_terminal:view_any', 'pos_terminal:view', 'pos_terminal:create', 'pos_terminal:update', 'pos_terminal:delete', 'pos_terminal:assign_operator',
            'pos:process_sales', 'pos:view_sales_reports',
            // Reportes
            'report:view_sales', 'report:view_commissions', 'report:view_network_growth', 'report:view_inventory',
            // Configuración de la Plataforma
            'setting:manage_general', 'setting:manage_payment_gateways', 'setting:manage_notifications',
            // Funcionalidades para Socios Multinivel
            'socio:view_dashboard', 'socio:view_profile', 'socio:update_profile', 'socio:view_downline', 'socio:view_commissions', 'socio:view_rank', 'socio:place_orders', 'socio:generate_referral_link',
            // Funcionalidades para Clientes
            'customer:view_profile', 'customer:update_profile', 'customer:view_orders', 'customer:place_orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('Default permissions seeded.');
    }
}
