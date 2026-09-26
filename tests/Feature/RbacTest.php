<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        // Define temporary test route with role middleware
        Route::get('/test-super-admin-only', function () {
            return 'super-admin-ok';
        })->middleware(['auth', 'role:super_admin']);

        Route::get('/test-warehouse-only', function () {
            return 'warehouse-ok';
        })->middleware(['auth', 'role:warehouse_staff']);
    }

    public function test_user_role_assignment_and_has_role(): void
    {
        $admin = User::where('email', 'admin@inventory.local')->first();
        $warehouse = User::where('email', 'warehouse@inventory.local')->first();
        $purchasing = User::where('email', 'purchasing@inventory.local')->first();
        $manager = User::where('email', 'manager@inventory.local')->first();

        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertFalse($admin->hasRole('warehouse_staff'));

        $this->assertTrue($warehouse->hasRole('warehouse_staff'));
        $this->assertTrue($purchasing->hasRole('purchasing'));
        $this->assertTrue($manager->hasRole('manager'));
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $admin = User::where('email', 'admin@inventory.local')->first();

        $this->assertTrue($admin->hasPermission('master-data.delete'));
        $this->assertTrue($admin->hasPermission('inventory.stock-in'));
        $this->assertTrue($admin->hasPermission('any-custom-permission'));
    }

    public function test_role_middleware_authorizes_correct_role(): void
    {
        $admin = User::where('email', 'admin@inventory.local')->first();
        $warehouse = User::where('email', 'warehouse@inventory.local')->first();

        $response = $this->actingAs($admin)->get('/test-super-admin-only');
        $response->assertStatus(200);

        $response = $this->actingAs($warehouse)->get('/test-warehouse-only');
        $response->assertStatus(200);
    }

    public function test_role_middleware_forbids_incorrect_role(): void
    {
        $warehouse = User::where('email', 'warehouse@inventory.local')->first();

        $response = $this->actingAs($warehouse)->get('/test-super-admin-only');
        $response->assertStatus(403);
    }
}
