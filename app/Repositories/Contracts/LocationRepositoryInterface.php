<?php

namespace App\Repositories\Contracts;

use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LocationRepositoryInterface
{
    public function paginateByWarehouse(Warehouse $warehouse, int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    public function create(Warehouse $warehouse, array $data): Location;
    public function update(Location $location, array $data): Location;
    public function delete(Location $location): bool;
}
