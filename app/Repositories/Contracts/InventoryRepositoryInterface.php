<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface
{
    public function getPaginatedInventory(int $perPage = 20, ?string $search = null): LengthAwarePaginator;
    public function getPaginatedMovements(int $perPage = 20, ?string $search = null): LengthAwarePaginator;
    public function firstOrCreate(array $attributes, array $values = []);
    public function getLockedForUpdate(int $productId, int $locationId);
    public function update($inventory, array $data);
    public function createMovement(array $data);
    public function getTotalQuantityByProduct(int $productId): int;
}
