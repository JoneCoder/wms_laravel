<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Organization;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Permissions
        $permissions = [
            'manage_users',
            'manage_roles',
            'view_inventory',
            'receive_inventory',
            'dispatch_inventory',
            'transfer_inventory',
            'manage_products',
            'manage_warehouses'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 2. Get the default Organization
        $org = Organization::where('name', 'Demo Organization')->first();

        // 3. Create Admin Role for Organization
        $adminRole = Role::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'admin'
        ]);
        // Give all permissions to admin
        $adminRole->permissions()->sync(Permission::all());

        // 4. Create Warehouse Operator Role
        $operatorRole = Role::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'warehouse_operator'
        ]);
        $operatorRole->permissions()->sync(
            Permission::whereIn('name', [
                'view_inventory',
                'receive_inventory',
                'dispatch_inventory',
                'transfer_inventory'
            ])->get()
        );
    }
}
