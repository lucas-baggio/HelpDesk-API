<?php

namespace Tests\Feature\Domains\User;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_user(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Atendente->value,
        ]);

        $response->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_create_user_with_jwt(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $this->loginToken($admin, 'password123');

        $response = $this->withToken($token)->postJson('/api/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Tecnico->value,
        ]);

        $response
            ->assertStatus(HttpStatus::CREATED)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                    'role' => UserRole::Tecnico->value,
                    'is_active' => true,
                ],
            ]);
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $atendente = User::factory()->create([
            'role' => UserRole::Atendente->value,
            'password' => Hash::make('password123'),
        ]);

        $token = $this->loginToken($atendente, 'password123');

        $response = $this->withToken($token)->postJson('/api/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Atendente->value,
        ]);

        $response->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_it_defaults_role_to_atendente_when_omitted(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $this->loginToken($admin, 'password123');

        $response = $this->withToken($token)->postJson('/api/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(HttpStatus::CREATED);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => UserRole::Atendente->value,
        ]);
    }

    public function test_create_user_validation_returns_standard_envelope(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $this->loginToken($admin, 'password123');

        $response = $this->withToken($token)->postJson('/api/users', []);

        $response
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'name',
                    'email',
                    'password',
                ],
            ]);
    }

    public function test_admin_can_update_user_with_jwt(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('password123'),
        ]);
        $user = User::factory()->create([
            'name' => 'Original Name',
            'role' => UserRole::Atendente->value,
        ]);

        $token = $this->loginToken($admin, 'password123');

        $response = $this->withToken($token)->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
            'role' => UserRole::Tecnico->value,
            'is_active' => false,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => 'Updated Name',
                    'role' => UserRole::Tecnico->value,
                    'is_active' => false,
                ],
            ]);
    }

    private function loginToken(User $user, string $password): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        return $response->json('data.access_token');
    }
}
