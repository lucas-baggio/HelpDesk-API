<?php

namespace Tests\Feature\Domains\Auth;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_in_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Admin->value,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'email',
                        'role',
                    ],
                ],
            ]);

        $this->assertSame('bearer', $response->json('data.token_type'));
    }

    public function test_it_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(HttpStatus::UNAUTHORIZED)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid email or password.',
            ]);
    }

    public function test_it_rejects_inactive_users(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(HttpStatus::FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'This user account is inactive.',
            ]);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $token = $login->json('data.access_token');

        $response = $this->withToken($token)->getJson('/api/auth/me');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Authenticated user retrieved successfully',
                'data' => [
                    'id' => $user->id,
                    'email' => 'admin@example.com',
                    'role' => UserRole::Admin->value,
                ],
            ]);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(HttpStatus::UNAUTHORIZED);
    }
}
