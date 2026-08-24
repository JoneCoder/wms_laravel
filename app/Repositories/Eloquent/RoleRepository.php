<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function getAllWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }
    
    public function create(array $data): Role
    {
        return Role::create($data);
    }
    
    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role;
    }
    
    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->permissions()->sync($permissions);
    }
}
