<?php

namespace App\Services;

use App\DTOs\ReceiveStockDTO;
use App\DTOs\TransferStockDTO;
use App\DTOs\DispatchStockDTO;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Jobs\CheckLowStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryService
{
    public function __construct(
        protected InventoryRepositoryInterface $inventoryRepository
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
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $dto->product_id, 'location_id' => $dto->location_id],
                ['quantity' => 0]
            );

            // Pessimistic locking to prevent race conditions during update
            $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->first();
            $inventory->quantity += $dto->quantity;
            $inventory->save();

            $movement = StockMovement::create([
                'product_id' => $dto->product_id,
                'destination_location_id' => $dto->location_id,
                'quantity' => $dto->quantity,
                'type' => 'receive',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            $this->triggerLowStockCheck($dto->product_id);

            return ['inventory' => $inventory, 'movement' => $movement];
        });
    }

    public function transfer(TransferStockDTO $dto, ?string $referenceNumber = null)
    {
        return DB::transaction(function () use ($dto, $referenceNumber) {
            // Lock source inventory
            $sourceInventory = Inventory::where('product_id', $dto->product_id)
                ->where('location_id', $dto->source_location_id)
                ->lockForUpdate()
                ->first();

            if (!$sourceInventory || $sourceInventory->quantity < $dto->quantity) {
                throw new \Exception('Insufficient inventory at source location.');
            }

            // Ensure destination exists and lock it
            $destInventory = Inventory::firstOrCreate(
                ['product_id' => $dto->product_id, 'location_id' => $dto->destination_location_id],
                ['quantity' => 0]
            );
            $destInventory = Inventory::where('id', $destInventory->id)->lockForUpdate()->first();

            // Perform transfer
            $sourceInventory->quantity -= $dto->quantity;
            $sourceInventory->save();

            $destInventory->quantity += $dto->quantity;
            $destInventory->save();

            $movement = StockMovement::create([
                'product_id' => $dto->product_id,
                'source_location_id' => $dto->source_location_id,
                'destination_location_id' => $dto->destination_location_id,
                'quantity' => $dto->quantity,
                'type' => 'transfer',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            return ['source_inventory' => $sourceInventory, 'destination_inventory' => $destInventory, 'movement' => $movement];
        });
    }

    public function dispatchStock(DispatchStockDTO $dto, ?string $referenceNumber = null)
    {
        return DB::transaction(function () use ($dto, $referenceNumber) {
            $inventory = Inventory::where('product_id', $dto->product_id)
                ->where('location_id', $dto->location_id)
                ->lockForUpdate()
                ->first();

            if (!$inventory || $inventory->quantity < $dto->quantity) {
                throw new \Exception('Insufficient inventory.');
            }

            $inventory->quantity -= $dto->quantity;
            $inventory->save();

            $movement = StockMovement::create([
                'product_id' => $dto->product_id,
                'source_location_id' => $dto->location_id,
                'quantity' => $dto->quantity,
                'type' => 'dispatch',
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
            ]);

            $this->triggerLowStockCheck($dto->product_id);

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
