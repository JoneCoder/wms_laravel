<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org = Organization::where('name', 'Demo Organization')->first();
        $adminRole = Role::where('name', 'admin')->where('organization_id', $org->id)->first();
        $operatorRole = Role::where('name', 'warehouse_operator')->where('organization_id', $org->id)->first();

        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'organization_id' => $org->id,
                'role_id' => $adminRole->id
            ]
        );

        // Operator User
        User::firstOrCreate(
            ['email' => 'operator@example.com'],
            [
                'name' => 'Warehouse Operator',
                'password' => Hash::make('password'),
                'organization_id' => $org->id,
                'role_id' => $operatorRole->id
            ]
        );
    }
}
