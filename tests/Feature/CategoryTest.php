<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->user = User::where('email', 'admin@inventory.local')->first();
    }

    public function test_authenticated_user_can_view_category_list(): void
    {
        Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Elektronik');
        $response->assertSee('Total Kategori');
    }

    public function test_category_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Jaringan Komputer',
            'slug' => 'jaringan-komputer',
            'description' => 'Perangkat dan kabel jaringan',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Jaringan Komputer',
            'slug' => 'jaringan-komputer',
            'is_active' => true,
        ]);
    }

    public function test_category_creation_fails_with_duplicate_name(): void
    {
        Category::create(['name' => 'Hardware', 'slug' => 'hardware', 'is_active' => true]);

        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Hardware',
            'slug' => 'hardware-2',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_can_be_updated(): void
    {
        $category = Category::create(['name' => 'Lama', 'slug' => 'lama', 'is_active' => true]);

        $response = $this->actingAs($this->user)->put(route('categories.update', $category), [
            'name' => 'Baru Diperbarui',
            'slug' => 'baru-diperbarui',
            'description' => 'Deskripsi baru',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Baru Diperbarui',
            'slug' => 'baru-diperbarui',
        ]);
    }

    public function test_category_can_be_soft_deleted(): void
    {
        $category = Category::create(['name' => 'Akan Dihapus', 'slug' => 'akan-dihapus', 'is_active' => true]);

        $response = $this->actingAs($this->user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_category_status_can_be_toggled(): void
    {
        $category = Category::create(['name' => 'Toggle Cat', 'slug' => 'toggle-cat', 'is_active' => true]);

        $response = $this->actingAs($this->user)->patch(route('categories.toggle-status', $category));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }
}
