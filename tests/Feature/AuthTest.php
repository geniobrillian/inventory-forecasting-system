<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Sistem');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::where('email', 'admin@inventory.local')->first();

        $response = $this->post('/login', [
            'email' => 'admin@inventory.local',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $this->post('/login', [
            'email' => 'admin@inventory.local',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::where('email', 'warehouse@inventory.local')->first();
        $user->update(['is_active' => false]);

        $response = $this->post('/login', [
            'email' => 'warehouse@inventory.local',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('email', 'admin@inventory.local')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::where('email', 'admin@inventory.local')->first();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Admin Updated',
            'email' => 'admin_updated@inventory.local',
            'phone' => '081299998888',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Admin Updated',
            'email' => 'admin_updated@inventory.local',
            'phone' => '081299998888',
        ]);
    }

    public function test_authenticated_user_can_update_password(): void
    {
        $user = User::where('email', 'admin@inventory.local')->first();

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'password',
            'password' => 'new-secret-password-123',
            'password_confirmation' => 'new-secret-password-123',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertTrue(Hash::check('new-secret-password-123', $user->password));
    }
}
