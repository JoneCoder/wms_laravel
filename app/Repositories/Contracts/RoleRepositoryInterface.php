<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function getAllWithPermissions(): Collection;
    
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    
    public function create(array $data): Role;
    
    public function update(Role $role, array $data): Role;
    
    public function syncPermissions(Role $role, array $permissions): void;
}
