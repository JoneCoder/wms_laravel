<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Location;
use App\Models\Warehouse;

class InventoryConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_receive_stock_successfully()
    {
        $warehouse = Warehouse::create(['name' => 'Main', 'code' => 'W1']);
        $location = Location::create(['warehouse_id' => $warehouse->id, 'code' => 'L1', 'name' => 'A1']);
        $product = Product::create(['sku' => 'P1', 'name' => 'Widget', 'unit' => 'pcs']);

        $response = $this->postJson('/api/v1/inventory/receive', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.quantity', 100);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'destination_location_id' => $location->id,
            'quantity' => 100,
            'type' => 'receive',
        ]);
    }
}
