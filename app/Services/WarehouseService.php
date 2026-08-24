<?php

namespace App\Services;

use App\DTOs\WarehouseDTO;
use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseService
{
    public function __construct(
        protected WarehouseRepositoryInterface $warehouseRepository
    ) {}

    public function getWarehouses(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->warehouseRepository->paginate($perPage, $search);
    }

    public function createWarehouse(WarehouseDTO $dto): Warehouse
    {
        return $this->warehouseRepository->create($dto->toArray());
    }

    public function updateWarehouse(Warehouse $warehouse, WarehouseDTO $dto): Warehouse
    {
        return $this->warehouseRepository->update($warehouse, $dto->toArray());
    }

    public function deleteWarehouse(Warehouse $warehouse): bool
    {
        return $this->warehouseRepository->delete($warehouse);
    }
}
