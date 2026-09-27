<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterDataSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_data_tables_exist_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasColumns('categories', [
            'id', 'name', 'slug', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at',
        ]));

        $this->assertTrue(Schema::hasTable('units'));
        $this->assertTrue(Schema::hasColumns('units', [
            'id', 'name', 'code', 'symbol', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at',
        ]));

        $this->assertTrue(Schema::hasTable('suppliers'));
        $this->assertTrue(Schema::hasColumns('suppliers', [
            'id', 'code', 'name', 'contact_person', 'phone', 'email', 'address', 'default_lead_time_days', 'is_active', 'created_at', 'updated_at', 'deleted_at',
        ]));

        $this->assertTrue(Schema::hasTable('warehouses'));
        $this->assertTrue(Schema::hasColumns('warehouses', [
            'id', 'code', 'name', 'address', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at',
        ]));

        $this->assertTrue(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasColumns('products', [
            'id', 'sku', 'name', 'category_id', 'unit_id', 'supplier_id', 'purchase_price', 'selling_price', 'minimum_stock', 'lead_time_days', 'forecast_method', 'is_active', 'created_at', 'updated_at', 'deleted_at',
        ]));

        $this->assertTrue(Schema::hasTable('warehouse_locations'));
        $this->assertTrue(Schema::hasColumns('warehouse_locations', [
            'id', 'warehouse_id', 'code', 'name', 'type', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_master_data_models_and_relationships(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic components and devices',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Pieces',
            'code' => 'PCS',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'PT Global Supplier',
            'contact_person' => 'Budi Santoso',
            'phone' => '08123456789',
            'email' => 'supplier@example.com',
            'address' => 'Jl. Industri No. 10, Jakarta',
            'default_lead_time_days' => 5,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Gudang Utama',
            'address' => 'Jl. Pergudangan Blok A1',
            'is_active' => true,
        ]);

        $location = $warehouse->locations()->create([
            'code' => 'RAK-01',
            'name' => 'Rak Utama 1',
            'type' => 'RACK',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'PRD-EL-001',
            'name' => 'Smart Sensor Module',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'supplier_id' => $supplier->id,
            'purchase_price' => 150000.00,
            'selling_price' => 200000.00,
            'minimum_stock' => 10,
            'lead_time_days' => 7,
            'forecast_method' => 'MOVING_AVERAGE',
            'is_active' => true,
        ]);

        // Assert relations
        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);

        $this->assertInstanceOf(Unit::class, $product->unit);
        $this->assertEquals($unit->id, $product->unit->id);

        $this->assertInstanceOf(Supplier::class, $product->supplier);
        $this->assertEquals($supplier->id, $product->supplier->id);

        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($unit->products->contains($product));
        $this->assertTrue($supplier->products->contains($product));

        $this->assertCount(1, $warehouse->locations);
        $this->assertEquals($location->id, $warehouse->locations->first()->id);
    }
}

