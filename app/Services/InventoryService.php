<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use App\Jobs\CheckLowStock;

class InventoryService
{
    public function receive(int $productId, int $locationId, int $quantity, ?string $referenceNumber = null)
    {
        return DB::transaction(function () use ($productId, $locationId, $quantity, $referenceNumber) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'location_id' => $locationId],
                ['quantity' => 0]
            );

            // Pessimistic locking to prevent race conditions during update
            $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->first();
            $inventory->quantity += $quantity;
            $inventory->save();

            $movement = StockMovement::create([
                'product_id' => $productId,
                'destination_location_id' => $locationId,
                'quantity' => $quantity,
                'type' => 'receive',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            $this->triggerLowStockCheck($productId);

            return ['inventory' => $inventory, 'movement' => $movement];
        });
    }

    public function transfer(int $productId, int $sourceLocationId, int $destinationLocationId, int $quantity, ?string $referenceNumber = null)
    {
        return DB::transaction(function () use ($productId, $sourceLocationId, $destinationLocationId, $quantity, $referenceNumber) {
            // Lock source inventory
            $sourceInventory = Inventory::where('product_id', $productId)
                ->where('location_id', $sourceLocationId)
                ->lockForUpdate()
                ->first();

            if (!$sourceInventory || $sourceInventory->quantity < $quantity) {
                throw new \Exception('Insufficient inventory at source location.');
            }

            // Ensure destination exists and lock it (lock order matters to avoid deadlocks, typically lock by ID asc, but here source vs dest is fine for simple use cases)
            $destInventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'location_id' => $destinationLocationId],
                ['quantity' => 0]
            );
            $destInventory = Inventory::where('id', $destInventory->id)->lockForUpdate()->first();

            // Perform transfer
            $sourceInventory->quantity -= $quantity;
            $sourceInventory->save();

            $destInventory->quantity += $quantity;
            $destInventory->save();

            $movement = StockMovement::create([
                'product_id' => $productId,
                'source_location_id' => $sourceLocationId,
                'destination_location_id' => $destinationLocationId,
                'quantity' => $quantity,
                'type' => 'transfer',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            return ['source_inventory' => $sourceInventory, 'destination_inventory' => $destInventory, 'movement' => $movement];
        });
    }

    public function dispatchStock(int $productId, int $sourceLocationId, int $quantity, ?string $referenceNumber = null)
    {
        return DB::transaction(function () use ($productId, $sourceLocationId, $quantity, $referenceNumber) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('location_id', $sourceLocationId)
                ->lockForUpdate()
                ->first();

            if (!$inventory || $inventory->quantity < $quantity) {
                throw new \Exception('Insufficient inventory.');
            }

            $inventory->quantity -= $quantity;
            $inventory->save();

            $movement = StockMovement::create([
                'product_id' => $productId,
                'source_location_id' => $sourceLocationId,
                'quantity' => $quantity,
                'type' => 'dispatch',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            $this->triggerLowStockCheck($productId);

            return ['inventory' => $inventory, 'movement' => $movement];
        });
    }

    protected function triggerLowStockCheck(int $productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $totalQty = Inventory::where('product_id', $productId)->sum('quantity');
            CheckLowStock::dispatch($product, $totalQty);
        }
    }
}
