<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function createTestUser(): User
    {
        return User::create([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@pharma.local',
            'password' => bcrypt('password'),
            'role' => 'pharmacist',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->createTestUser();

        $response = $this->postJson('/api/login', [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'username', 'name', 'email', 'role'],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'testuser');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createTestUser();

        $response = $this->postJson('/api/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_login_fails_with_nonexistent_username(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'nobody',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('data.username', 'testuser')
            ->assertJsonPath('data.role', 'pharmacist');
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_alert_send_returns_401(): void
    {
        $response = $this->postJson('/api/alerts/send', []);
        $response->assertStatus(401);
    }

    public function test_unauthenticated_orders_returns_401(): void
    {
        $response = $this->getJson('/api/orders?lot=951357');
        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = $this->createTestUser();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }
}
