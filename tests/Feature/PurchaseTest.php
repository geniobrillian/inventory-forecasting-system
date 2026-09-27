<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Product $product;
    protected PurchaseService $purchaseService;
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles
        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'description' => 'Super Admin Role',
            'permissions' => ['*'],
        ]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);

        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik', 'code' => 'ELK', 'is_active' => true]);
        $unit = Unit::create(['name' => 'Pcs', 'code' => 'PCS', 'symbol' => 'pcs', 'is_active' => true]);

        $this->warehouse = Warehouse::create([
            'code' => 'GUD-01',
            'name' => 'Gudang Utama',
            'address' => 'Jl. Industri No 1',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-01',
            'name' => 'PT Supplier Jaya',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'sku' => 'PROD-001',
            'name' => 'Sensor Suhu DHT22',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 50000,
            'selling_price' => 75000,
            'min_stock' => 10,
            'max_stock' => 200,
            'is_active' => true,
        ]);

        $this->purchaseService = app(PurchaseService::class);
        $this->inventoryService = app(InventoryService::class);
    }

    public function test_can_view_purchasing_index_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.index'));
        $response->assertStatus(200);
        $response->assertSee('Purchase Orders');
    }

    public function test_can_create_draft_purchase_order_without_changing_inventory_stock(): void
    {
        $initialStock = $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity;
        $this->assertEquals(0, $initialStock);

        $purchaseData = [
            'purchase_number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'DRAFT',
            'shipping_cost' => 10000,
            'notes' => 'Testing draft PO',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 50,
                    'unit_price' => 50000,
                    'discount_percent' => 0,
                    'tax_percent' => 10,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('purchasing.store'), $purchaseData);
        $response->assertRedirect();

        $this->assertDatabaseHas('purchases', [
            'purchase_number' => 'PO-TEST-001',
            'status' => 'DRAFT',
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'product_id' => $this->product->id,
            'quantity' => 50,
            'received_quantity' => 0,
        ]);

        // Invariant: Draft or ordered PO must NOT inflate warehouse stock!
        $afterStock = $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity;
        $this->assertEquals(0, $afterStock);
    }

    public function test_can_transition_po_to_ordered_status(): void
    {
        $purchase = $this->purchaseService->createPurchase([
            'purchase_number' => 'PO-TEST-002',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'DRAFT',
            'shipping_cost' => 0,
            'created_by' => $this->adminUser->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 25,
                    'unit_price' => 50000,
                ],
            ],
        ]);

        $response = $this->actingAs($this->adminUser)->patch(route('purchasing.order', $purchase));
        $response->assertRedirect(route('purchasing.show', $purchase));

        $purchase->refresh();
        $this->assertEquals('ORDERED', $purchase->status);

        // Stock is still 0 because physical items are not received yet
        $this->assertEquals(0, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);
    }

    public function test_receiving_items_atomically_increments_stock_and_creates_ledger(): void
    {
        $purchase = $this->purchaseService->createPurchase([
            'purchase_number' => 'PO-TEST-003',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'ORDERED',
            'shipping_cost' => 0,
            'created_by' => $this->adminUser->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 100,
                    'unit_price' => 50000,
                ],
            ],
        ]);

        $item = $purchase->items->first();

        // 1. Partial receive: 40 pcs
        $receiveDataPartial = [
            'received_date' => now()->format('Y-m-d'),
            'notes' => 'Received partial delivery',
            'items' => [
                [
                    'purchase_item_id' => $item->id,
                    'received_quantity' => 40,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('purchasing.receive.process', $purchase), $receiveDataPartial);
        $response->assertRedirect(route('purchasing.show', $purchase));

        $purchase->refresh();
        $this->assertEquals('PARTIALLY_RECEIVED', $purchase->status);
        $this->assertEquals(40, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // 2. Receive remaining 60 pcs
        $receiveDataFinal = [
            'received_date' => now()->format('Y-m-d'),
            'notes' => 'Received final delivery',
            'items' => [
                [
                    'purchase_item_id' => $item->id,
                    'received_quantity' => 60,
                ],
            ],
        ];

        $this->actingAs($this->adminUser)
            ->post(route('purchasing.receive.process', $purchase), $receiveDataFinal);

        $purchase->refresh();
        $this->assertEquals('RECEIVED', $purchase->status);
        $this->assertEquals(100, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Assert ledger transactions were created with type PURCHASE
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_type' => 'PURCHASE',
            'reference_id' => $purchase->purchase_number,
        ]);
    }
}
