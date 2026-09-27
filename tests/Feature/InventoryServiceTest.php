<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;
    private Product $product;
    private Warehouse $warehouseA;
    private Warehouse $warehouseB;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InventoryService::class);

        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Pieces',
            'code' => 'PCS',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'SSD NVMe 1TB',
            'sku' => 'SSD-1TB',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 500000,
            'selling_price' => 750000,
            'minimum_stock' => 10,
            'lead_time_days' => 5,
            'is_active' => true,
        ]);

        $this->warehouseA = Warehouse::create([
            'name' => 'Gudang Utama',
            'code' => 'WH-01',
            'is_active' => true,
        ]);

        $this->warehouseB = Warehouse::create([
            'name' => 'Gudang Cabang',
            'code' => 'WH-02',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create();
    }

    public function test_stock_in_creates_stock_and_transaction_ledger(): void
    {
        $stockTx = $this->service->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 50,
            type: 'PURCHASE',
            notes: 'Pembelian awal',
            userId: $this->user->id,
            referenceId: 'PO-001'
        );

        $stock = $this->service->getStock($this->product->id, $this->warehouseA->id);
        $this->assertEquals(50, $stock->quantity);
        $this->assertDatabaseHas('inventory_stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity' => 50,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'transaction_type' => 'PURCHASE',
            'quantity' => 50,
            'stock_before' => 0,
            'stock_after' => 50,
            'reference_id' => 'PO-001',
            'performed_by' => $this->user->id,
        ]);
    }

    public function test_stock_out_reduces_stock_and_records_ledger(): void
    {
        $this->service->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 100,
            type: 'PURCHASE',
            notes: 'Initial',
            userId: $this->user->id,
            referenceId: 'PO-001'
        );

        $this->service->stockOut(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 30,
            type: 'SALE',
            notes: 'Penjualan retail',
            userId: $this->user->id,
            referenceId: 'INV-001'
        );

        $stock = $this->service->getStock($this->product->id, $this->warehouseA->id);
        $this->assertEquals(70, $stock->quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'transaction_type' => 'SALE',
            'quantity' => 30,
            'stock_before' => 100,
            'stock_after' => 70,
            'reference_id' => 'INV-001',
        ]);
    }

    public function test_stock_out_throws_insufficient_stock_exception_when_deficit(): void
    {
        $this->service->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 20,
            type: 'PURCHASE',
            notes: 'Initial',
            userId: $this->user->id,
            referenceId: 'PO-001'
        );

        $this->expectException(InsufficientStockException::class);

        $this->service->stockOut(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 50,
            type: 'SALE',
            notes: 'Over request',
            userId: $this->user->id,
            referenceId: 'INV-DEFICIT'
        );
    }

    public function test_transfer_moves_stock_atomically_between_warehouses(): void
    {
        $this->service->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 100,
            type: 'PURCHASE',
            notes: 'Initial',
            userId: $this->user->id,
            referenceId: 'PO-001'
        );

        $result = $this->service->transfer(
            productId: $this->product->id,
            fromWarehouseId: $this->warehouseA->id,
            toWarehouseId: $this->warehouseB->id,
            quantity: 40,
            notes: 'Relokasi stok',
            userId: $this->user->id,
            referenceId: 'TRF-001'
        );

        $this->assertEquals(60, $result['from_stock']->quantity);
        $this->assertEquals(40, $result['to_stock']->quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'transaction_type' => 'TRANSFER_OUT',
            'quantity' => 40,
            'stock_before' => 100,
            'stock_after' => 60,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseB->id,
            'transaction_type' => 'TRANSFER_IN',
            'quantity' => 40,
            'stock_before' => 0,
            'stock_after' => 40,
        ]);
    }

    public function test_transfer_fails_when_from_and_to_warehouse_are_same(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->transfer(
            productId: $this->product->id,
            fromWarehouseId: $this->warehouseA->id,
            toWarehouseId: $this->warehouseA->id,
            quantity: 10
        );
    }

    public function test_adjustment_increases_stock_when_actual_is_higher(): void
    {
        $this->service->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 50,
            type: 'PURCHASE',
            notes: 'Initial',
            userId: $this->user->id,
            referenceId: 'PO-001'
        );

        $this->service->adjust(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            actualQuantity: 65,
            reason: 'Ditemukan barang lebih',
            userId: $this->user->id,
            referenceId: 'OPN-001'
        );

        $stock = $this->service->getStock($this->product->id, $this->warehouseA->id);
        $this->assertEquals(65, $stock->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'transaction_type' => 'ADJUSTMENT_IN',
            'quantity' => 15,
            'stock_before' => 50,
            'stock_after' => 65,
        ]);
    }

    public function test_adjustment_decreases_stock_when_actual_is_lower(): void
    {
        $this->service->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            quantity: 50,
            type: 'PURCHASE',
            notes: 'Initial',
            userId: $this->user->id,
            referenceId: 'PO-001'
        );

        $this->service->adjust(
            productId: $this->product->id,
            warehouseId: $this->warehouseA->id,
            actualQuantity: 42,
            reason: 'Barang rusak / hilang',
            userId: $this->user->id,
            referenceId: 'OPN-002'
        );

        $stock = $this->service->getStock($this->product->id, $this->warehouseA->id);
        $this->assertEquals(42, $stock->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'transaction_type' => 'ADJUSTMENT_OUT',
            'quantity' => 8,
            'stock_before' => 50,
            'stock_after' => 42,
        ]);
    }
}
