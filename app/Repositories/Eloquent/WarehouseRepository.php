<?php

namespace App\Repositories\Eloquent;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return Warehouse::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
            })
            ->paginate($perPage);
    }

    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);
        return $warehouse;
    }

    public function delete(Warehouse $warehouse): bool
    {
        return $warehouse->delete();
    }
}
