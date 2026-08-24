<?php

namespace App\Repositories\Contracts;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WarehouseRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    public function create(array $data): Warehouse;
    public function update(Warehouse $warehouse, array $data): Warehouse;
    public function delete(Warehouse $warehouse): bool;
}
