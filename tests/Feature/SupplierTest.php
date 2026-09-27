<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->user = User::where('email', 'admin@inventory.local')->first();
    }

    public function test_authenticated_user_can_view_suppliers_list(): void
    {
        Supplier::create([
            'code' => 'SUP-001',
            'name' => 'PT Sumber Rezeki',
            'default_lead_time_days' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('SUP-001');
        $response->assertSee('PT Sumber Rezeki');
    }

    public function test_supplier_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('suppliers.store'), [
            'code' => 'sup-abc',
            'name' => 'PT Maju Terus',
            'contact_person' => 'Ahmad',
            'phone' => '081233445566',
            'email' => 'contact@majuterus.com',
            'address' => 'Jl. Merdeka No. 45',
            'default_lead_time_days' => 7,
            'is_active' => '1',
        ]);

        $supplier = Supplier::where('code', 'SUP-ABC')->first();
        $this->assertNotNull($supplier);
        $response->assertRedirect(route('suppliers.show', $supplier));
        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP-ABC',
            'name' => 'PT Maju Terus',
            'default_lead_time_days' => 7,
        ]);
    }

    public function test_supplier_creation_fails_with_invalid_lead_time(): void
    {
        $response = $this->actingAs($this->user)->post(route('suppliers.store'), [
            'code' => 'SUP-XYZ',
            'name' => 'PT XYZ',
            'default_lead_time_days' => -3,
        ]);

        $response->assertSessionHasErrors('default_lead_time_days');
    }

    public function test_supplier_show_page_can_be_rendered(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-SHOW',
            'name' => 'PT Vendor Unggul',
            'contact_person' => 'Siti',
            'phone' => '0811223344',
            'email' => 'siti@vendor.com',
            'address' => 'Jl. Industri',
            'default_lead_time_days' => 4,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('suppliers.show', $supplier));

        $response->assertStatus(200);
        $response->assertSee('SUP-SHOW');
        $response->assertSee('PT Vendor Unggul');
        $response->assertSee('Parameter Pengadaan');
    }

    public function test_supplier_can_be_updated(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-OLD',
            'name' => 'Nama Lama',
            'default_lead_time_days' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->put(route('suppliers.update', $supplier), [
            'code' => 'SUP-NEW',
            'name' => 'Nama Baru',
            'default_lead_time_days' => 8,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'code' => 'SUP-NEW',
            'name' => 'Nama Baru',
            'default_lead_time_days' => 8,
        ]);
    }

    public function test_supplier_can_be_soft_deleted(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-DEL',
            'name' => 'Hapus Supplier',
            'default_lead_time_days' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->delete(route('suppliers.destroy', $supplier));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_supplier_status_can_be_toggled(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-TOG',
            'name' => 'Toggle Supplier',
            'default_lead_time_days' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->patch(route('suppliers.toggle-status', $supplier));

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'is_active' => false,
        ]);
    }
}
