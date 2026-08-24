<?php

namespace App\Services;

use App\DTOs\LocationDTO;
use App\Models\Location;
use App\Models\Warehouse;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocationService
{
    public function __construct(
        protected LocationRepositoryInterface $locationRepository
    ) {}

    public function getLocations(Warehouse $warehouse, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->locationRepository->paginateByWarehouse($warehouse, $perPage, $search);
    }

    public function createLocation(Warehouse $warehouse, LocationDTO $dto): Location
    {
        return $this->locationRepository->create($warehouse, $dto->toArray());
    }

    public function updateLocation(Location $location, LocationDTO $dto): Location
    {
        return $this->locationRepository->update($location, $dto->toArray());
    }

    public function deleteLocation(Location $location): bool
    {
        return $this->locationRepository->delete($location);
    }
}
