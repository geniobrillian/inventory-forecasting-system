<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AnalyticsService;
use App\Services\InventoryService;
use App\Services\SalesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Warehouse $warehouse;
    protected Product $productA;
    protected Product $productB;
    protected Product $productC;
    protected AnalyticsService $analyticsService;
    protected InventoryService $inventoryService;
    protected SalesService $salesService;

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

        $category = Category::create(['name' => 'Hardware', 'slug' => 'hardware', 'code' => 'HDW', 'is_active' => true]);
        $unit = Unit::create(['name' => 'Pcs', 'code' => 'PCS', 'symbol' => 'pcs', 'is_active' => true]);

        $this->warehouse = Warehouse::create([
            'code' => 'GUD-CENTRAL',
            'name' => 'Gudang Sentral',
            'is_active' => true,
        ]);

        // Product A: Healthy stock (100 units, min 20)
        $this->productA = Product::create([
            'sku' => 'SKU-AAA',
            'name' => 'Product Healthy',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'minimum_stock' => 20,
            'is_active' => true,
        ]);

        // Product B: Low Stock (15 units, min 20)
        $this->productB = Product::create([
            'sku' => 'SKU-BBB',
            'name' => 'Product Low',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 20000,
            'selling_price' => 30000,
            'minimum_stock' => 20,
            'is_active' => true,
        ]);

        // Product C: Out of Stock (0 units, min 10)
        $this->productC = Product::create([
            'sku' => 'SKU-CCC',
            'name' => 'Product Out',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 50000,
            'selling_price' => 70000,
            'minimum_stock' => 10,
            'is_active' => true,
        ]);

        $this->analyticsService = app(AnalyticsService::class);
        $this->inventoryService = app(InventoryService::class);
        $this->salesService = app(SalesService::class);

        // Stock in for Product A (100) & Product B (15)
        $this->inventoryService->stockIn(
            productId: $this->productA->id,
            warehouseId: $this->warehouse->id,
            quantity: 100,
            type: 'INITIAL',
            userId: $this->adminUser->id
        );

        $this->inventoryService->stockIn(
            productId: $this->productB->id,
            warehouseId: $this->warehouse->id,
            quantity: 15,
            type: 'INITIAL',
            userId: $this->adminUser->id
        );
    }

    public function test_authenticated_user_can_view_analytics_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Analytics Dashboard');
        $response->assertSee('Total SKU Produk');
        $response->assertSee('Total Valuasi Stok');
    }

    public function test_analytics_service_calculates_kpi_and_valuations_accurately(): void
    {
        $kpi = $this->analyticsService->getKpiMetrics();

        // 3 products
        $this->assertEquals(3, $kpi['total_products']);

        // Total stock: 100 (A) + 15 (B) = 115
        $this->assertEquals(115, $kpi['total_stock_quantity']);

        // Valuation: (100 * 10,000) + (15 * 20,000) = 1,000,000 + 300,000 = 1,300,000
        $this->assertEquals(1300000, $kpi['total_inventory_value']);
    }

    public function test_stock_health_counts_accurately_classifies_products(): void
    {
        $health = $this->analyticsService->getStockHealthCounts();

        // Product A: 100 > min 20 => Healthy
        $this->assertEquals(1, $health['healthy']);

        // Product B: 15 <= min 20 (and > 20*0.4=8) => Low Stock
        $this->assertEquals(1, $health['low_stock']);

        // Product C: 0 => Out of Stock
        $this->assertEquals(1, $health['out_of_stock']);
    }

    public function test_velocity_metrics_identifies_fast_moving_and_dead_stock(): void
    {
        // Execute sale on Product A (25 units) to make it Fast-Moving
        $this->salesService->createSale([
            'invoice_number' => 'INV-VELOCITY-01',
            'warehouse_id' => $this->warehouse->id,
            'customer_name' => 'Fast Buyer',
            'sale_date' => now()->format('Y-m-d'),
            'status' => 'COMPLETED',
            'created_by' => $this->adminUser->id,
            'items' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity' => 25,
                    'unit_price' => 15000,
                ],
            ],
        ]);

        $velocity = $this->analyticsService->getVelocityMetrics();

        // Product A sold >= 20 units in last 30d => fast moving
        $this->assertCount(1, $velocity['fast_moving']);
        $this->assertEquals($this->productA->id, $velocity['fast_moving']->first()->id);
        $this->assertEquals(25, $velocity['fast_moving']->first()->sold_30d);
    }

    public function test_demand_trend_and_stock_movement_charts_return_valid_series(): void
    {
        $demandChart = $this->analyticsService->getDemandTrendChartData(14);
        $this->assertCount(14, $demandChart['labels']);
        $this->assertCount(1, $demandChart['series']);
        $this->assertCount(14, $demandChart['series'][0]['data']);

        $movementChart = $this->analyticsService->getStockMovementChartData(6);
        $this->assertCount(6, $movementChart['labels']);
        $this->assertCount(2, $movementChart['series']);
    }

    public function test_stock_card_filters_by_transaction_type(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('inventory.stock-card', [
            'product_id' => $this->productA->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'INITIAL',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kartu Stok');
        $response->assertSee('SKU-AAA');
    }
}
