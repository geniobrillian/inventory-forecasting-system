<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasingSalesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Product $product;
    protected PurchaseService $purchaseService;
    protected SalesService $salesService;
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'description' => 'Super Admin Role',
            'permissions' => ['*'],
        ]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);

        $category = Category::create(['name' => 'Komponen', 'slug' => 'komponen', 'code' => 'KMP', 'is_active' => true]);
        $unit = Unit::create(['name' => 'Box', 'code' => 'BOX', 'symbol' => 'box', 'is_active' => true]);

        $this->warehouse = Warehouse::create([
            'code' => 'GUD-CENTRAL',
            'name' => 'Gudang Pusat',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-GLOBAL',
            'name' => 'Global Component Supplier',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'sku' => 'SKU-LIFECYCLE-01',
            'name' => 'Integrated Microcontroller Unit',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 100000,
            'selling_price' => 150000,
            'min_stock' => 10,
            'max_stock' => 500,
            'is_active' => true,
        ]);

        $this->purchaseService = app(PurchaseService::class);
        $this->salesService = app(SalesService::class);
        $this->inventoryService = app(InventoryService::class);
    }

    public function test_complete_purchasing_and_sales_flow_with_stock_synchronization(): void
    {
        // Step 0: Stock is 0
        $this->assertEquals(0, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Step 1: Procurement PO of 100 units
        $po = $this->purchaseService->createPurchase([
            'purchase_number' => 'PO-INTEGRATION-01',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'ORDERED',
            'created_by' => $this->adminUser->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 100,
                    'unit_cost' => 100000,
                ],
            ],
        ]);

        // Stock still 0
        $this->assertEquals(0, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Step 2: Receive physical goods (100 units)
        $this->purchaseService->receiveItems($po, [
            'received_date' => now()->format('Y-m-d'),
            'notes' => 'Full container delivery',
            'items' => [
                [
                    'purchase_item_id' => $po->items->first()->id,
                    'received_quantity' => 100,
                ],
            ],
        ]);

        // Stock is now 100
        $this->assertEquals(100, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Step 3: Create draft sale of 20 units
        $sale1 = $this->salesService->createSale([
            'invoice_number' => 'INV-INTEGRATION-01',
            'warehouse_id' => $this->warehouse->id,
            'customer_name' => 'Client A',
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'DRAFT',
            'payment_method' => 'TRANSFER',
            'payment_status' => 'UNPAID',
            'created_by' => $this->adminUser->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                    'unit_price' => 150000,
                ],
            ],
        ]);

        // Draft sale doesn't touch stock (still 100)
        $this->assertEquals(100, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Step 4: Complete Sale 1
        $this->salesService->completeSale($sale1);

        // Stock is now 80 (100 - 20)
        $this->assertEquals(80, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Step 5: Create direct completed sale of 30 units
        $sale2 = $this->salesService->createSale([
            'invoice_number' => 'INV-INTEGRATION-02',
            'warehouse_id' => $this->warehouse->id,
            'customer_name' => 'Client B',
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'COMPLETED',
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'paid_amount' => 4500000,
            'created_by' => $this->adminUser->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 30,
                    'unit_price' => 150000,
                ],
            ],
        ]);

        // Stock is now 50 (80 - 30)
        $this->assertEquals(50, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Step 6: Cancel Sale 2 (Returned)
        $this->salesService->cancelSale($sale2, 'Defective order cancellation & stock return');

        // Stock is restored to 80 (50 + 30)
        $this->assertEquals(80, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Step 7: Verify ledger history (Stock Card)
        $transactions = $this->inventoryService->getStockCard(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id
        );

        // Expected transactions:
        // 1. PURCHASE (+100)
        // 2. SALE (-20)
        // 3. SALE (-30)
        // 4. RETURN_IN (+30)
        $this->assertCount(4, $transactions);
    }
}
