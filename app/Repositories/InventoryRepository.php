<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryRepository
{
    /**
     * Receive stock into a location.
     */
    public function receiveStock(int $productId, int $locationId, int $quantity, ?int $userId = null)
    {
        return DB::transaction(function () use ($productId, $locationId, $quantity, $userId) {
            // Use lockForUpdate to prevent concurrent updates from overriding this operation
            $inventory = Inventory::lockForUpdate()->firstOrCreate(
                ['product_id' => $productId, 'location_id' => $locationId],
                ['quantity' => 0]
            );

            $inventory->quantity += $quantity;
            $inventory->save();

            StockMovement::create([
                'product_id' => $productId,
                'destination_location_id' => $locationId,
                'quantity' => $quantity,
                'type' => 'receive',
                'user_id' => $userId,
            ]);

            return $inventory;
        });
    }

    /**
     * Transfer stock between locations.
     */
    public function transferStock(int $productId, int $sourceLocationId, int $destinationLocationId, int $quantity, ?int $userId = null)
    {
        return DB::transaction(function () use ($productId, $sourceLocationId, $destinationLocationId, $quantity, $userId) {
            // Lock the source and destination inventories
            $sourceInventory = Inventory::lockForUpdate()->where([
                'product_id' => $productId,
                'location_id' => $sourceLocationId,
            ])->first();

            if (!$sourceInventory || $sourceInventory->quantity < $quantity) {
                throw new \Exception('Insufficient inventory at source location.');
            }

            $destInventory = Inventory::lockForUpdate()->firstOrCreate(
                ['product_id' => $productId, 'location_id' => $destinationLocationId],
                ['quantity' => 0]
            );

            $sourceInventory->quantity -= $quantity;
            $sourceInventory->save();

            $destInventory->quantity += $quantity;
            $destInventory->save();

            StockMovement::create([
                'product_id' => $productId,
                'source_location_id' => $sourceLocationId,
                'destination_location_id' => $destinationLocationId,
                'quantity' => $quantity,
                'type' => 'transfer',
                'user_id' => $userId,
            ]);

            return $destInventory;
        });
    }

    /**
     * Dispatch stock from a location.
     */
    public function dispatchStock(int $productId, int $locationId, int $quantity, ?int $userId = null)
    {
        return DB::transaction(function () use ($productId, $locationId, $quantity, $userId) {
            $inventory = Inventory::lockForUpdate()->where([
                'product_id' => $productId,
                'location_id' => $locationId,
            ])->first();

            if (!$inventory || $inventory->quantity < $quantity) {
                throw new \Exception('Insufficient inventory.');
            }

            $inventory->quantity -= $quantity;
            $inventory->save();

            StockMovement::create([
                'product_id' => $productId,
                'source_location_id' => $locationId,
                'quantity' => $quantity,
                'type' => 'dispatch',
                'user_id' => $userId,
            ]);

            return $inventory;
        });
    }
}
