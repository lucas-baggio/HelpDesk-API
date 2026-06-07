<?php

namespace Tests\Feature\Domains\User;

use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_user_with_standard_success_envelope(): void
    {
        $response = $this->postJson('/api/users', [
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
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => UserRole::Tecnico->value,
        ]);
    }

    public function test_it_defaults_role_to_atendente_when_omitted(): void
    {
        $response = $this->postJson('/api/users', [
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
        $response = $this->postJson('/api/users', []);

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

    public function test_it_updates_user_with_standard_success_envelope(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'role' => UserRole::Atendente->value,
        ]);

        $response = $this->putJson("/api/users/{$user->id}", [
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
}
