<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\DTOs\RegisterDTO;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected OrganizationRepositoryInterface $organizationRepository,
        protected RoleRepositoryInterface $roleRepository,
        protected PermissionRepositoryInterface $permissionRepository
    ) {}

    public function registerUser(RegisterDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            // Create Organization
            $organization = $this->organizationRepository->create([
                'name' => $dto->organization_name,
            ]);

            // Create Admin Role for this Organization
            $adminRole = $this->roleRepository->create([
                'organization_id' => $organization->id,
                'name' => 'admin',
            ]);

            // Create Warehouse Operator Role
            $operatorRole = $this->roleRepository->create([
                'organization_id' => $organization->id,
                'name' => 'warehouse_operator',
            ]);

            // Assign permissions to roles
            $allPermissions = $this->permissionRepository->getAll();
            
            // Admin gets all permissions
            $adminRole->permissions()->attach($allPermissions->pluck('id')->toArray());

            // Operator gets inventory specific permissions
            $operatorPermissions = $allPermissions->whereIn('name', [
                'view_inventory', 
                'receive_inventory', 
                'transfer_inventory', 
                'dispatch_inventory'
            ])->pluck('id')->toArray();
            
            $operatorRole->permissions()->attach($operatorPermissions);

            // Create User
            $user = $this->userRepository->create([
                'organization_id' => $organization->id,
                'role_id' => $adminRole->id,
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Load user relationships for the response
            $user->load('role.permissions', 'organization');

            return [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ];
        });
    }

    public function loginUser(LoginDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Load user relationships for the response
        $user->load('role.permissions', 'organization');

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
