<?php

namespace App\Services;

use App\DTOs\ReceiveStockDTO;
use App\DTOs\TransferStockDTO;
use App\DTOs\DispatchStockDTO;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Jobs\CheckLowStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryService
{
    public function __construct(
        protected InventoryRepositoryInterface $inventoryRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getInventory(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->inventoryRepository->getPaginatedInventory($perPage, $search);
    }

    public function getMovements(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->inventoryRepository->getPaginatedMovements($perPage, $search);
    }
    public function receive(ReceiveStockDTO $dto, ?string $referenceNumber = null)
    {
        return DB::transaction(function () use ($dto, $referenceNumber) {
            $this->inventoryRepository->firstOrCreate(
                ['product_id' => $dto->product_id, 'location_id' => $dto->location_id],
                ['quantity' => 0]
            );

            // Pessimistic locking to prevent race conditions during update
            $inventory = $this->inventoryRepository->getLockedForUpdate($dto->product_id, $dto->location_id);
            $this->inventoryRepository->update($inventory, [
                'quantity' => $inventory->quantity + $dto->quantity
            ]);

            $movement = $this->inventoryRepository->createMovement([
                'product_id' => $dto->product_id,
                'destination_location_id' => $dto->location_id,
                'quantity' => $dto->quantity,
                'type' => 'receive',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            $this->triggerLowStockCheck($dto->product_id);
            $this->clearInventoryCache();

            return ['inventory' => $inventory, 'movement' => $movement];
        });
    }

    public function transfer(TransferStockDTO $dto, ?string $referenceNumber = null)
    {
        return DB::transaction(function () use ($dto, $referenceNumber) {
            // Lock source inventory
            $sourceInventory = $this->inventoryRepository->getLockedForUpdate($dto->product_id, $dto->source_location_id);

            if (!$sourceInventory || $sourceInventory->quantity < $dto->quantity) {
                throw new \Exception('Insufficient inventory at source location.');
            }

            // Ensure destination exists and lock it
            $this->inventoryRepository->firstOrCreate(
                ['product_id' => $dto->product_id, 'location_id' => $dto->destination_location_id],
                ['quantity' => 0]
            );
            $destInventory = $this->inventoryRepository->getLockedForUpdate($dto->product_id, $dto->destination_location_id);

            // Perform transfer
            $this->inventoryRepository->update($sourceInventory, [
                'quantity' => $sourceInventory->quantity - $dto->quantity
            ]);

            $this->inventoryRepository->update($destInventory, [
                'quantity' => $destInventory->quantity + $dto->quantity
            ]);

            $movement = $this->inventoryRepository->createMovement([
                'product_id' => $dto->product_id,
                'source_location_id' => $dto->source_location_id,
                'destination_location_id' => $dto->destination_location_id,
                'quantity' => $dto->quantity,
                'type' => 'transfer',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            $this->clearInventoryCache();

            return ['source_inventory' => $sourceInventory, 'destination_inventory' => $destInventory, 'movement' => $movement];
        });
    }

    public function dispatchStock(DispatchStockDTO $dto, ?string $referenceNumber = null)
    {
        return DB::transaction(function () use ($dto, $referenceNumber) {
            $inventory = $this->inventoryRepository->getLockedForUpdate($dto->product_id, $dto->location_id);

            if (!$inventory || $inventory->quantity < $dto->quantity) {
                throw new \Exception('Insufficient inventory.');
            }

            $this->inventoryRepository->update($inventory, [
                'quantity' => $inventory->quantity - $dto->quantity
            ]);

            $movement = $this->inventoryRepository->createMovement([
                'product_id' => $dto->product_id,
                'source_location_id' => $dto->location_id,
                'quantity' => $dto->quantity,
                'type' => 'dispatch',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            $this->triggerLowStockCheck($dto->product_id);
            $this->clearInventoryCache();

            return ['inventory' => $inventory, 'movement' => $movement];
        });
    }

    protected function triggerLowStockCheck(int $productId)
    {
        $product = $this->productRepository->findById($productId);

        if ($product) {
            $totalQty = $this->inventoryRepository->getTotalQuantityByProduct($productId);
            CheckLowStock::dispatch($product, $totalQty);
        }
    }

    protected function clearInventoryCache()
    {
        if (auth()->check() && auth()->user()->organization_id) {
            Cache::tags(['inventory_org_' . auth()->user()->organization_id])->flush();
        }
    }
}
