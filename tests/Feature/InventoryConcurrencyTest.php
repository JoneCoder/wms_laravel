<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class InventoryConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_receive_stock_successfully()
    {
        $org = Organization::create(['name' => 'Org']);
        
        $role = Role::create(['name' => 'admin', 'organization_id' => $org->id]);
        $permission = Permission::create(['name' => 'receive_inventory']);
        $role->permissions()->attach($permission->id);
        
        $user = User::factory()->create([
            'organization_id' => $org->id,
            'role_id' => $role->id,
        ]);
        
        $this->actingAs($user);

        $warehouse = Warehouse::create(['name' => 'Main', 'code' => 'W1', 'organization_id' => $org->id]);
        $location = Location::create(['warehouse_id' => $warehouse->id, 'code' => 'L1', 'name' => 'A1', 'organization_id' => $org->id]);
        $product = Product::create(['sku' => 'P1', 'name' => 'Widget', 'unit' => 'pcs', 'organization_id' => $org->id]);

        $response = $this->postJson('/api/v1/inventory/receive', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.inventory.quantity', 100);

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
