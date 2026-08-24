<?php

namespace App\Services;

use App\DTOs\RoleDTO;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {}

    public function paginateRoles(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->roleRepository->paginate($perPage, $search);
    }

    public function getAllRoles()
    {
        return $this->roleRepository->getAllWithPermissions();
    }

    public function createRole(RoleDTO $roleDTO, int $organizationId): Role
    {
        return DB::transaction(function () use ($roleDTO, $organizationId) {
            $data = $roleDTO->toArray();
            $data['organization_id'] = $organizationId;
            
            $role = $this->roleRepository->create($data);

            if ($roleDTO->permissions !== null && !empty($roleDTO->permissions)) {
                $this->roleRepository->syncPermissions($role, $roleDTO->permissions);
            }

            return $role;
        });
    }

    public function updateRole(Role $role, RoleDTO $roleDTO): Role
    {
        return DB::transaction(function () use ($role, $roleDTO) {
            if ($roleDTO->name !== null) {
                $this->roleRepository->update($role, $roleDTO->toArray());
            }

            if ($roleDTO->permissions !== null) {
                $this->roleRepository->syncPermissions($role, $roleDTO->permissions);
            }

            return $role;
        });
    }
}
