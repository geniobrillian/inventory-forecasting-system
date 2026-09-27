<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;
    private Unit $unit;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->user = User::where('email', 'admin@inventory.local')->first();

        $this->category = Category::create(['name' => 'Hardware', 'slug' => 'hardware', 'is_active' => true]);
        $this->unit = Unit::create(['name' => 'Pieces', 'code' => 'PCS', 'symbol' => 'pcs', 'is_active' => true]);
        $this->supplier = Supplier::create(['name' => 'PT Supplier', 'code' => 'SUP-01', 'default_lead_time_days' => 5, 'is_active' => true]);
    }

    public function test_authenticated_user_can_view_product_list(): void
    {
        Product::create([
            'sku' => 'SKU-001',
            'name' => 'Intel Core i7 13700K',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 5000000,
            'selling_price' => 6000000,
            'minimum_stock' => 5,
            'lead_time_days' => 5,
            'forecast_method' => 'MOVING_AVERAGE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('SKU-001');
        $response->assertSee('Intel Core i7 13700K');
    }

    public function test_product_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'sku' => 'sku-amd-ryzen',
            'name' => 'AMD Ryzen 7 7800X3D',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 6000000,
            'selling_price' => 7200000,
            'minimum_stock' => 10,
            'lead_time_days' => 7,
            'forecast_method' => 'EXPONENTIAL_SMOOTHING',
            'is_active' => '1',
        ]);

        $product = Product::where('sku', 'SKU-AMD-RYZEN')->first();
        $this->assertNotNull($product);
        $response->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-AMD-RYZEN',
            'name' => 'AMD Ryzen 7 7800X3D',
            'forecast_method' => 'EXPONENTIAL_SMOOTHING',
            'minimum_stock' => 10,
            'lead_time_days' => 7,
        ]);
    }

    public function test_product_validation_rules(): void
    {
        // Negative price and duplicate SKU
        Product::create([
            'sku' => 'EXISTING-SKU',
            'name' => 'Existing',
            'purchase_price' => 100,
            'selling_price' => 120,
            'minimum_stock' => 1,
            'lead_time_days' => 1,
            'forecast_method' => 'MOVING_AVERAGE',
        ]);

        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'sku' => 'EXISTING-SKU',
            'name' => 'Invalid',
            'purchase_price' => -100,
            'selling_price' => -50,
            'minimum_stock' => -5,
            'lead_time_days' => -1,
            'forecast_method' => 'INVALID_METHOD',
        ]);

        $response->assertSessionHasErrors(['sku', 'purchase_price', 'selling_price', 'minimum_stock', 'lead_time_days', 'forecast_method']);
    }

    public function test_product_show_page_can_be_rendered(): void
    {
        $product = Product::create([
            'sku' => 'PRD-TEST-SHOW',
            'name' => 'Display Test Product',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 100000,
            'selling_price' => 150000,
            'minimum_stock' => 20,
            'lead_time_days' => 3,
            'forecast_method' => 'MOVING_AVERAGE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('PRD-TEST-SHOW');
        $response->assertSee('Display Test Product');
        $response->assertSee('Finansial & Margin', false);
    }

    public function test_product_can_be_updated(): void
    {
        $product = Product::create([
            'sku' => 'PRD-OLD-SKU',
            'name' => 'Nama Lama',
            'purchase_price' => 100,
            'selling_price' => 150,
            'minimum_stock' => 5,
            'lead_time_days' => 5,
            'forecast_method' => 'MOVING_AVERAGE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->put(route('products.update', $product), [
            'sku' => 'PRD-NEW-SKU',
            'name' => 'Nama Baru Update',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 200,
            'selling_price' => 300,
            'minimum_stock' => 10,
            'lead_time_days' => 4,
            'forecast_method' => 'EXPONENTIAL_SMOOTHING',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('products.show', $product));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'PRD-NEW-SKU',
            'name' => 'Nama Baru Update',
            'purchase_price' => 200,
            'selling_price' => 300,
        ]);
    }

    public function test_product_can_be_soft_deleted(): void
    {
        $product = Product::create([
            'sku' => 'PRD-DELETE-ME',
            'name' => 'Delete Me',
            'purchase_price' => 10,
            'selling_price' => 20,
            'minimum_stock' => 1,
            'lead_time_days' => 1,
            'forecast_method' => 'MOVING_AVERAGE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_status_can_be_toggled(): void
    {
        $product = Product::create([
            'sku' => 'PRD-TOGGLE',
            'name' => 'Toggle Status',
            'purchase_price' => 10,
            'selling_price' => 20,
            'minimum_stock' => 1,
            'lead_time_days' => 1,
            'forecast_method' => 'MOVING_AVERAGE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->patch(route('products.toggle-status', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_product_search_and_filters(): void
    {
        Product::create([
            'sku' => 'PRD-SRCH-A',
            'name' => 'Kabel UTP Gigabit',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'minimum_stock' => 1,
            'lead_time_days' => 1,
            'forecast_method' => 'MOVING_AVERAGE',
            'is_active' => true,
        ]);

        Product::create([
            'sku' => 'PRD-SRCH-B',
            'name' => 'Keyboard Mechanical',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 5000,
            'selling_price' => 8000,
            'minimum_stock' => 1,
            'lead_time_days' => 1,
            'forecast_method' => 'MOVING_AVERAGE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['search' => 'Kabel']));
        $response->assertStatus(200);
        $response->assertSee('Kabel UTP Gigabit');
        $response->assertDontSee('Keyboard Mechanical');
    }
}
