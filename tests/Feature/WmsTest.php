<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Role;

class WmsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $product;
    protected $sourceLocation;
    protected $destLocation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::create(['name' => 'Test Org']);
        $role = Role::create(['organization_id' => $this->organization->id, 'name' => 'admin']);
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $role->id,
        ]);

        $this->actingAs($this->user);

        $warehouse = Warehouse::create([
            'code' => 'W1',
            'name' => 'Main Warehouse',
            'organization_id' => $this->organization->id,
        ]);

        $this->sourceLocation = Location::create([
            'warehouse_id' => $warehouse->id,
            'code' => 'L1',
            'name' => 'Aisle 1',
            'organization_id' => $this->organization->id,
        ]);

        $this->destLocation = Location::create([
            'warehouse_id' => $warehouse->id,
            'code' => 'L2',
            'name' => 'Aisle 2',
            'organization_id' => $this->organization->id,
        ]);

        $this->product = Product::create([
            'sku' => 'SKU-001',
            'name' => 'Test Product',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_can_receive_stock()
    {
        $response = $this->postJson('/api/inventory/receive', [
            'product_id' => $this->product->id,
            'location_id' => $this->sourceLocation->id,
            'quantity' => 100,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $this->product->id,
            'location_id' => $this->sourceLocation->id,
            'quantity' => 100,
        ]);
    }

    public function test_can_transfer_stock()
    {
        $service = app(InventoryService::class);
        $service->receive($this->product->id, $this->sourceLocation->id, 50);

        $response = $this->postJson('/api/inventory/transfer', [
            'product_id' => $this->product->id,
            'source_location_id' => $this->sourceLocation->id,
            'destination_location_id' => $this->destLocation->id,
            'quantity' => 20,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventories', [
            'location_id' => $this->sourceLocation->id,
            'quantity' => 30,
        ]);
        $this->assertDatabaseHas('inventories', [
            'location_id' => $this->destLocation->id,
            'quantity' => 20,
        ]);
    }

    public function test_cannot_dispatch_more_than_available()
    {
        $service = app(InventoryService::class);
        $service->receive($this->product->id, $this->sourceLocation->id, 10);

        $response = $this->postJson('/api/inventory/dispatch', [
            'product_id' => $this->product->id,
            'source_location_id' => $this->sourceLocation->id,
            'quantity' => 20,
        ]);

        $response->assertStatus(400); // Because we catch exception and return 400
        $this->assertDatabaseHas('inventories', [
            'location_id' => $this->sourceLocation->id,
            'quantity' => 10,
        ]);
    }

    public function test_concurrency_dispatch()
    {
        $service = app(InventoryService::class);
        $service->receive($this->product->id, $this->sourceLocation->id, 10);

        // Since true concurrency is hard in simple phpunit, we simulate a race 
        // condition where two transactions try to read and update.
        // But our DB transactions and lockForUpdate() prevent it.
        // We will just verify that the service throws an exception when over-dispatching
        try {
            DB::transaction(function () use ($service) {
                $service->dispatchStock($this->product->id, $this->sourceLocation->id, 8);
                $service->dispatchStock($this->product->id, $this->sourceLocation->id, 7);
            });
            $this->fail('Expected exception for insufficient inventory');
        } catch (\Exception $e) {
            $this->assertEquals('Insufficient inventory.', $e->getMessage());
        }
    }
}
