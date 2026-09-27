<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockCardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Product $product;
    private Warehouse $warehouse;
    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InventoryService::class);

        $role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'is_system' => true,
        ]);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole($role);

        $category = Category::create([
            'name' => 'Peripheral',
            'slug' => 'peripheral',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Pcs',
            'code' => 'PCS',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Keyboard Mechanical RGB',
            'sku' => 'KBD-RGB',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 300000,
            'selling_price' => 500000,
            'minimum_stock' => 10,
            'lead_time_days' => 5,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Pusat',
            'code' => 'WH-PST',
            'is_active' => true,
        ]);
    }

    public function test_stock_card_page_renders_with_no_product_selected(): void
    {
        $response = $this->actingAs($this->admin)->get(route('inventory.stock-card'));
        $response->assertStatus(200);
        $response->assertSee('Kartu Stok (Stock Card Ledger)');
        $response->assertSee('Pilih Produk Terlebih Dahulu');
    }

    public function test_stock_card_calculates_initial_balance_and_running_ledger(): void
    {
        // 1. Transaction 10 days ago: In +100
        $this->service->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            quantity: 100,
            type: 'PURCHASE',
            notes: 'Old purchase',
            userId: $this->admin->id,
            referenceId: 'PO-HISTORIC',
            date: Carbon::now()->subDays(10)->startOfDay()
        );

        // 2. Transaction 8 days ago: Out -20
        $this->service->stockOut(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            quantity: 20,
            type: 'SALE',
            notes: 'Old sale',
            userId: $this->admin->id,
            referenceId: 'INV-HISTORIC',
            date: Carbon::now()->subDays(8)->startOfDay()
        );

        // 3. Transaction 2 days ago (within filter): In +50
        $this->service->stockIn(
            productId: $this->product->id,
            warehouseId: $this->warehouse->id,
            quantity: 50,
            type: 'PURCHASE',
            notes: 'Recent inbound',
            userId: $this->admin->id,
            referenceId: 'PO-RECENT',
            date: Carbon::now()->subDays(2)->startOfDay()
        );

        // Filter from 5 days ago to today
        $startDate = Carbon::now()->subDays(5)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('inventory.stock-card', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $response->assertStatus(200);
        // Initial balance before 5 days ago was 100 - 20 = 80.00
        $response->assertSee('80.00');
        // Final balance should be 80 + 50 = 130.00
        $response->assertSee('130.00');
        $response->assertSee('PO-RECENT');
    }
}
