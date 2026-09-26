<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Roles
        $superAdminRole = Role::firstOrCreate(['slug' => 'super_admin'], [
            'name' => 'Super Admin',
            'description' => 'Administrator dengan hak akses penuh ke seluruh sistem.',
        ]);

        $warehouseRole = Role::firstOrCreate(['slug' => 'warehouse_staff'], [
            'name' => 'Warehouse Staff',
            'description' => 'Staff operasional pergudangan untuk mutasi dan kartu stok.',
        ]);

        $purchasingRole = Role::firstOrCreate(['slug' => 'purchasing'], [
            'name' => 'Purchasing Staff',
            'description' => 'Staff pengadaan untuk mengelola supplier dan purchase order.',
        ]);

        $managerRole = Role::firstOrCreate(['slug' => 'manager'], [
            'name' => 'Manager',
            'description' => 'Manajer untuk memantau analytics, forecasting, dan laporan.',
        ]);

        // 2. Create Permissions
        $permissions = [
            // Master Data
            ['name' => 'View Master Data', 'slug' => 'master-data.view', 'group' => 'master-data'],
            ['name' => 'Create Master Data', 'slug' => 'master-data.create', 'group' => 'master-data'],
            ['name' => 'Edit Master Data', 'slug' => 'master-data.edit', 'group' => 'master-data'],
            ['name' => 'Delete Master Data', 'slug' => 'master-data.delete', 'group' => 'master-data'],

            // Inventory
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'group' => 'inventory'],
            ['name' => 'Stock In', 'slug' => 'inventory.stock-in', 'group' => 'inventory'],
            ['name' => 'Stock Out', 'slug' => 'inventory.stock-out', 'group' => 'inventory'],
            ['name' => 'Transfer Stock', 'slug' => 'inventory.transfer', 'group' => 'inventory'],
            ['name' => 'Adjust Stock', 'slug' => 'inventory.adjust', 'group' => 'inventory'],
            ['name' => 'View Stock Card', 'slug' => 'inventory.stock-card', 'group' => 'inventory'],

            // Purchasing
            ['name' => 'View Purchases', 'slug' => 'purchasing.view', 'group' => 'purchasing'],
            ['name' => 'Create Purchase Order', 'slug' => 'purchasing.create', 'group' => 'purchasing'],
            ['name' => 'Receive Purchase Order', 'slug' => 'purchasing.receive', 'group' => 'purchasing'],

            // Sales
            ['name' => 'View Sales', 'slug' => 'sales.view', 'group' => 'sales'],
            ['name' => 'Create Sale', 'slug' => 'sales.create', 'group' => 'sales'],

            // Forecasting & Replenishment
            ['name' => 'View Forecasting', 'slug' => 'forecasting.view', 'group' => 'forecasting'],
            ['name' => 'Calculate Forecasting', 'slug' => 'forecasting.calculate', 'group' => 'forecasting'],
            ['name' => 'View Replenishment', 'slug' => 'replenishment.view', 'group' => 'replenishment'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'group' => 'reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'group' => 'reports'],

            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'users'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // 3. Assign Permissions to Roles
        // Warehouse Staff
        $warehouseRole->permissions()->sync(
            Permission::whereIn('slug', [
                'master-data.view',
                'inventory.view',
                'inventory.stock-in',
                'inventory.stock-out',
                'inventory.transfer',
                'inventory.adjust',
                'inventory.stock-card',
                'purchasing.receive',
            ])->pluck('id')
        );

        // Purchasing Staff
        $purchasingRole->permissions()->sync(
            Permission::whereIn('slug', [
                'master-data.view',
                'purchasing.view',
                'purchasing.create',
                'replenishment.view',
                'reports.view',
            ])->pluck('id')
        );

        // Manager
        $managerRole->permissions()->sync(
            Permission::whereIn('slug', [
                'master-data.view',
                'inventory.view',
                'inventory.stock-card',
                'purchasing.view',
                'sales.view',
                'forecasting.view',
                'forecasting.calculate',
                'replenishment.view',
                'reports.view',
                'reports.export',
            ])->pluck('id')
        );

        // 4. Create Default Users for each Role
        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@inventory.local',
                'phone' => '081200000001',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Warehouse Staff Officer',
                'email' => 'warehouse@inventory.local',
                'phone' => '081200000002',
                'password' => Hash::make('password'),
                'role' => 'warehouse_staff',
            ],
            [
                'name' => 'Purchasing Officer',
                'email' => 'purchasing@inventory.local',
                'phone' => '081200000003',
                'password' => Hash::make('password'),
                'role' => 'purchasing',
            ],
            [
                'name' => 'Operational Manager',
                'email' => 'manager@inventory.local',
                'phone' => '081200000004',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ],
        ];

        foreach ($users as $userData) {
            $roleSlug = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(['email' => $userData['email']], $userData);
            $user->assignRole($roleSlug);
        }
    }
}
