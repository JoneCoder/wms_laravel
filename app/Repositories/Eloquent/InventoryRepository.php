<?php

namespace App\Repositories\Eloquent;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function getPaginatedInventory(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $orgId = auth()->user()->organization_id;
        $page = request()->get('page', 1);
        $searchKey = $search ? "_search_" . md5($search) : "";
        $cacheKey = "inventory_org_{$orgId}_page_{$page}_per_{$perPage}{$searchKey}";

        return Cache::remember($cacheKey, 60, function () use ($perPage, $search) {
            return Inventory::with(['product', 'location.warehouse'])
                ->when($search, function ($query, $search) {
                    $query->whereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%");
                    })->orWhereHas('location', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                    });
                })
                ->paginate($perPage);
        });
    }

    public function getPaginatedMovements(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return StockMovement::with(['product', 'sourceLocation', 'destinationLocation', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('reference_number', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhereHas('product', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                      });
            })
            ->latest()
            ->paginate($perPage);
    }
}
