<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->user = User::where('email', 'admin@inventory.local')->first();
    }

    public function test_authenticated_user_can_view_warehouses_list(): void
    {
        Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Gudang Utama Jakarta',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('warehouses.index'));

        $response->assertStatus(200);
        $response->assertSee('WH-01');
        $response->assertSee('Gudang Utama Jakarta');
    }

    public function test_warehouse_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('warehouses.store'), [
            'code' => 'wh-bdg01',
            'name' => 'Gudang Bandung',
            'address' => 'Jl. Soekarno Hatta No. 12',
            'description' => 'Hub Jawa Barat',
            'is_active' => '1',
        ]);

        $warehouse = Warehouse::where('code', 'WH-BDG01')->first();
        $this->assertNotNull($warehouse);
        $response->assertRedirect(route('warehouses.show', $warehouse));
        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH-BDG01',
            'name' => 'Gudang Bandung',
        ]);
    }

    public function test_warehouse_locations_can_be_managed(): void
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-LOC',
            'name' => 'Gudang Lokasi Test',
            'is_active' => true,
        ]);

        // Add location
        $response = $this->actingAs($this->user)->post(route('warehouses.locations.store', $warehouse), [
            'code' => 'rak-a1',
            'name' => 'Rak A1 Tingkat 1',
            'type' => 'RACK',
            'description' => 'Untuk barang berat',
        ]);

        $response->assertRedirect(route('warehouses.show', $warehouse));
        $this->assertDatabaseHas('warehouse_locations', [
            'warehouse_id' => $warehouse->id,
            'code' => 'RAK-A1',
            'name' => 'Rak A1 Tingkat 1',
            'type' => 'RACK',
        ]);

        $location = $warehouse->locations()->first();

        // Update location
        $updateResponse = $this->actingAs($this->user)->put(
            route('warehouses.locations.update', [$warehouse, $location]),
            [
                'code' => 'RAK-A1-UPDATED',
                'name' => 'Rak A1 Diperbarui',
                'type' => 'ZONE',
            ]
        );

        $updateResponse->assertRedirect(route('warehouses.show', $warehouse));
        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $location->id,
            'code' => 'RAK-A1-UPDATED',
            'type' => 'ZONE',
        ]);

        // Toggle location status
        $this->actingAs($this->user)->patch(route('warehouses.locations.toggle-status', [$warehouse, $location]));
        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $location->id,
            'is_active' => false,
        ]);

        // Delete location
        $deleteResponse = $this->actingAs($this->user)->delete(route('warehouses.locations.destroy', [$warehouse, $location]));
        $deleteResponse->assertRedirect(route('warehouses.show', $warehouse));
        $this->assertSoftDeleted('warehouse_locations', ['id' => $location->id]);
    }

    public function test_warehouse_can_be_soft_deleted(): void
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-DEL',
            'name' => 'Gudang Hapus',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->delete(route('warehouses.destroy', $warehouse));

        $response->assertRedirect(route('warehouses.index'));
        $this->assertSoftDeleted('warehouses', ['id' => $warehouse->id]);
    }

    public function test_warehouse_status_can_be_toggled(): void
    {
        $warehouse = Warehouse::create([
            'code' => 'WH-TOG',
            'name' => 'Toggle Warehouse',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->patch(route('warehouses.toggle-status', $warehouse));

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'is_active' => false,
        ]);
    }
}
