<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryStockTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Product $product;
    private Warehouse $warehouseA;
    private Warehouse $warehouseB;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrator',
            'is_system' => true,
        ]);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole($role);

        $category = Category::create([
            'name' => 'Komputer',
            'slug' => 'komputer',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Pcs',
            'code' => 'PCS',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Monitor LED 24 Inch',
            'sku' => 'MON-24',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 1000000,
            'selling_price' => 1500000,
            'minimum_stock' => 5,
            'lead_time_days' => 3,
            'is_active' => true,
        ]);

        $this->warehouseA = Warehouse::create([
            'name' => 'Gudang Jakarta',
            'code' => 'WH-JKT',
            'is_active' => true,
        ]);

        $this->warehouseB = Warehouse::create([
            'name' => 'Gudang Bandung',
            'code' => 'WH-BDG',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_inventory_pages(): void
    {
        $this->get(route('inventory.overview'))->assertRedirect(route('login'));
        $this->get(route('inventory.stock-in'))->assertRedirect(route('login'));
        $this->get(route('inventory.stock-out'))->assertRedirect(route('login'));
        $this->get(route('inventory.transfer'))->assertRedirect(route('login'));
        $this->get(route('inventory.adjustment'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_inventory_overview(): void
    {
        $response = $this->actingAs($this->admin)->get(route('inventory.overview'));
        $response->assertStatus(200);
        $response->assertSee('Ringkasan Inventori');
        $response->assertSee($this->product->name);
    }

    public function test_admin_can_perform_stock_in(): void
    {
        $response = $this->actingAs($this->admin)->post(route('inventory.stock-in.process'), [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 75,
            'transaction_date' => now()->format('Y-m-d'),
            'reference_id' => 'PO-TEST-01',
            'notes' => 'Penerimaan batch test',
        ]);

        $response->assertRedirect(route('inventory.overview'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 75,
        ]);
    }

    public function test_admin_can_perform_stock_out(): void
    {
        // Add initial stock
        app(InventoryService::class)->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 50,
            type: 'PURCHASE',
            userId: $this->admin->id,
            referenceId: 'PO-01'
        );

        $response = $this->actingAs($this->admin)->post(route('inventory.stock-out.process'), [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 20,
            'transaction_date' => now()->format('Y-m-d'),
            'reference_id' => 'DO-TEST-01',
            'notes' => 'Pengeluaran invoice 01',
        ]);

        $response->assertRedirect(route('inventory.overview'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 30,
        ]);
    }

    public function test_stock_out_validation_fails_on_deficit(): void
    {
        app(InventoryService::class)->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 10,
            type: 'PURCHASE',
            userId: $this->admin->id,
            referenceId: 'PO-01'
        );

        $response = $this->actingAs($this->admin)->post(route('inventory.stock-out.process'), [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 50, // exceeds 10
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_admin_can_perform_warehouse_transfer(): void
    {
        app(InventoryService::class)->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 100,
            type: 'PURCHASE',
            userId: $this->admin->id,
            referenceId: 'PO-01'
        );

        $response = $this->actingAs($this->admin)->post(route('inventory.transfer.process'), [
            'product_id' => $this->product->id,
            'from_warehouse_id' => $this->warehouseA->id,
            'to_warehouse_id' => $this->warehouseB->id,
            'quantity' => 35,
            'transaction_date' => now()->format('Y-m-d'),
            'reference_id' => 'TRF-TEST-01',
            'notes' => 'Transfer antar cabang',
        ]);

        $response->assertRedirect(route('inventory.overview'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 65,
        ]);

        $this->assertDatabaseHas('inventory_stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseB->id,
            'quantity' => 35,
        ]);
    }

    public function test_admin_can_perform_stock_adjustment(): void
    {
        app(InventoryService::class)->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 50,
            type: 'PURCHASE',
            userId: $this->admin->id,
            referenceId: 'PO-01'
        );

        $response = $this->actingAs($this->admin)->post(route('inventory.adjustment.process'), [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'actual_quantity' => 45,
            'transaction_date' => now()->format('Y-m-d'),
            'reference_id' => 'BA-OPN-TEST',
            'notes' => 'Selisih susut fisik',
        ]);

        $response->assertRedirect(route('inventory.overview'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 45,
        ]);
    }

    public function test_api_stock_lookup_returns_correct_json(): void
    {
        app(InventoryService::class)->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 88,
            type: 'PURCHASE',
            userId: $this->admin->id,
            referenceId: 'PO-01'
        );

        $response = $this->actingAs($this->admin)->get(route('inventory.api.stock', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 88,
            'unit' => 'PCS',
        ]);
    }
}
