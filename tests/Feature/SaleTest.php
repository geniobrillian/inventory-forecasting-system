<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Warehouse $warehouse;
    protected Product $product;
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

        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik', 'code' => 'ELK', 'is_active' => true]);
        $unit = Unit::create(['name' => 'Pcs', 'code' => 'PCS', 'symbol' => 'pcs', 'is_active' => true]);

        $this->warehouse = Warehouse::create([
            'code' => 'GUD-01',
            'name' => 'Gudang Utama',
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

        $this->salesService = app(SalesService::class);
        $this->inventoryService = app(InventoryService::class);

        // Pre-populate warehouse with 50 stock units
        $this->inventoryService->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            quantity: 50,
            type: 'INITIAL',
            userId: $this->adminUser->id
        );
    }

    public function test_can_view_sales_index_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('sales.index'));
        $response->assertStatus(200);
        $response->assertSee('Sales Orders');
    }

    public function test_can_create_draft_sale_without_deducting_stock(): void
    {
        $this->assertEquals(50, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        $saleData = [
            'invoice_number' => 'INV-TEST-001',
            'warehouse_id' => $this->warehouse->id,
            'customer_name' => 'PT Customer Hebat',
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'DRAFT',
            'payment_method' => 'TRANSFER',
            'payment_status' => 'UNPAID',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 75000,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('sales.store'), $saleData);
        $response->assertRedirect();

        $this->assertDatabaseHas('sales', [
            'invoice_number' => 'INV-TEST-001',
            'status' => 'DRAFT',
        ]);

        // Stock must still be 50
        $this->assertEquals(50, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);
    }

    public function test_creating_completed_sale_deducts_stock_and_creates_ledger(): void
    {
        $this->assertEquals(50, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        $saleData = [
            'invoice_number' => 'INV-TEST-002',
            'warehouse_id' => $this->warehouse->id,
            'customer_name' => 'Walk-in Customer',
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'COMPLETED',
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'paid_amount' => 150000,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 75000,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('sales.store'), $saleData);
        $response->assertRedirect();

        $this->assertDatabaseHas('sales', [
            'invoice_number' => 'INV-TEST-002',
            'status' => 'COMPLETED',
        ]);

        // Stock decreased from 50 to 48
        $this->assertEquals(48, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Assert ledger entry created with type SALE
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_type' => 'SALE',
            'reference_id' => 'INV-TEST-002',
            'quantity' => 2,
        ]);
    }

    public function test_sale_fails_when_stock_is_insufficient(): void
    {
        $this->expectException(InsufficientStockException::class);

        // Attempting to sell 100 units when only 50 exist in the warehouse
        $this->salesService->createSale([
            'invoice_number' => 'INV-TEST-003',
            'warehouse_id' => $this->warehouse->id,
            'customer_name' => 'Customer Greedy',
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'COMPLETED',
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'created_by' => $this->adminUser->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 100,
                    'unit_price' => 75000,
                ],
            ],
        ]);
    }

    public function test_cancelling_completed_sale_restores_stock_via_return_in(): void
    {
        $sale = $this->salesService->createSale([
            'invoice_number' => 'INV-TEST-004',
            'warehouse_id' => $this->warehouse->id,
            'customer_name' => 'Customer Batal',
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'COMPLETED',
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'created_by' => $this->adminUser->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 15,
                    'unit_price' => 75000,
                ],
            ],
        ]);

        $this->assertEquals(35, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Cancel the sale
        $response = $this->actingAs($this->adminUser)->patch(route('sales.cancel', $sale), [
            'cancellation_reason' => 'Customer changed mind',
        ]);
        $response->assertRedirect(route('sales.show', $sale));

        $sale->refresh();
        $this->assertEquals('CANCELLED', $sale->status);

        // Stock restored back to 50
        $this->assertEquals(50, $this->inventoryService->getStock($this->product->id, $this->warehouse->id)->quantity);

        // Transaction ledger record for RETURN_IN
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'transaction_type' => 'RETURN_IN',
            'reference_id' => $sale->invoice_number,
            'quantity' => 15,
        ]);
    }
}
