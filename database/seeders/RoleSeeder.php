<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the role / permission matrix for the admin guard.
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'settings.view',
            'settings.manage',
            'users.view',
            'users.manage',
            'products.view',
            'products.manage',
            'orders.view',
            'orders.manage',
            'marketing.view',
            'marketing.manage',
            'cms.view',
            'cms.manage',
            'seo.manage',
            'reports.view',
            'customers.view',
            'customers.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        $customer = Role::findOrCreate('customer', 'web');

        $superAdmin->givePermissionTo(Permission::all());
        $admin->givePermissionTo([
            'settings.view', 'settings.manage', 'users.view',
            'products.view', 'products.manage',
            'orders.view', 'orders.manage',
            'marketing.view', 'marketing.manage',
            'cms.view', 'cms.manage', 'seo.manage',
            'reports.view', 'customers.view', 'customers.manage',
        ]);
        $customer->syncPermissions([]);
    }
}
