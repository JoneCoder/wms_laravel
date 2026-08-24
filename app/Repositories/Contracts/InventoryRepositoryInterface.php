<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface
{
    public function getPaginatedInventory(int $perPage = 20, ?string $search = null): LengthAwarePaginator;
    public function getPaginatedMovements(int $perPage = 20, ?string $search = null): LengthAwarePaginator;
}
