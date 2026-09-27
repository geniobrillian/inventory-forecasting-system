<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->user = User::where('email', 'admin@inventory.local')->first();
    }

    public function test_authenticated_user_can_view_units_list(): void
    {
        Unit::create([
            'name' => 'Pieces',
            'code' => 'PCS',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('units.index'));

        $response->assertStatus(200);
        $response->assertSee('PCS');
        $response->assertSee('Pieces');
    }

    public function test_unit_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('units.store'), [
            'name' => 'Kilogram',
            'code' => 'kg',
            'symbol' => 'kg',
            'description' => 'Satuan berat kilogram',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', [
            'name' => 'Kilogram',
            'code' => 'KG', // auto uppercase
            'symbol' => 'kg',
            'is_active' => true,
        ]);
    }

    public function test_unit_creation_fails_with_duplicate_code(): void
    {
        Unit::create(['name' => 'Box', 'code' => 'BOX', 'is_active' => true]);

        $response = $this->actingAs($this->user)->post(route('units.store'), [
            'name' => 'Other Box',
            'code' => 'box',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_unit_can_be_updated(): void
    {
        $unit = Unit::create(['name' => 'Pack', 'code' => 'PCK', 'is_active' => true]);

        $response = $this->actingAs($this->user)->put(route('units.update', $unit), [
            'name' => 'Package Bundle',
            'code' => 'PACK',
            'symbol' => 'pk',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'name' => 'Package Bundle',
            'code' => 'PACK',
        ]);
    }

    public function test_unit_can_be_soft_deleted(): void
    {
        $unit = Unit::create(['name' => 'Meter', 'code' => 'MTR', 'is_active' => true]);

        $response = $this->actingAs($this->user)->delete(route('units.destroy', $unit));

        $response->assertRedirect(route('units.index'));
        $this->assertSoftDeleted('units', ['id' => $unit->id]);
    }

    public function test_unit_status_can_be_toggled(): void
    {
        $unit = Unit::create(['name' => 'Liter', 'code' => 'LTR', 'is_active' => true]);

        $response = $this->actingAs($this->user)->patch(route('units.toggle-status', $unit));

        $response->assertRedirect();
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'is_active' => false,
        ]);
    }
}
