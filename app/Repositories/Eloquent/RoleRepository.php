<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return Role::with('permissions')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate($perPage);
    }

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
