<?php

namespace App\Repositories\Eloquent;

use App\Models\Location;
use App\Models\Warehouse;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocationRepository implements LocationRepositoryInterface
{
    public function paginateByWarehouse(Warehouse $warehouse, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $warehouse->locations()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->paginate($perPage);
    }

    public function create(Warehouse $warehouse, array $data): Location
    {
        return $warehouse->locations()->create($data);
    }

    public function update(Location $location, array $data): Location
    {
        $location->update($data);
        return $location;
    }

    public function delete(Location $location): bool
    {
        return $location->delete();
    }
}
