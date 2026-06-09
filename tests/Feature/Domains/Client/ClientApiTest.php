<?php

namespace Tests\Feature\Domains\Client;

use App\Domains\Client\Models\Client;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_guest_cannot_create_client(): void
    {
        $response = $this->postJson('/api/clients', $this->clientPayload());

        $response->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_create_client(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->postJson('/api/clients', $this->clientPayload());

        $response
            ->assertStatus(HttpStatus::CREATED)
            ->assertJson([
                'success' => true,
                'message' => 'Client created successfully.',
                'data' => [
                    'name' => 'Acme Corp',
                    'email' => 'contato@acme.com',
                    'cpf_cnpj' => '12.345.678/0001-99',
                    'is_active' => true,
                ],
            ]);
    }

    public function test_atendente_can_create_client(): void
    {
        $token = $this->loginAs(UserRole::Atendente);

        $response = $this->withToken($token)->postJson('/api/clients', $this->clientPayload());

        $response->assertStatus(HttpStatus::CREATED);
    }

    public function test_tecnico_cannot_create_client(): void
    {
        $token = $this->loginAs(UserRole::Tecnico);

        $response = $this->withToken($token)->postJson('/api/clients', $this->clientPayload());

        $response->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_create_client_validation_returns_standard_envelope(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->postJson('/api/clients', []);

        $response
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJson(['success' => false])
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['name', 'email', 'cpf_cnpj', 'phone'],
            ]);
    }

    public function test_create_client_rejects_duplicate_cpf_cnpj(): void
    {
        Client::factory()->create(['cpf_cnpj' => '12.345.678/0001-99']);
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->postJson('/api/clients', $this->clientPayload());

        $response->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_guest_cannot_update_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->putJson("/api/clients/{$client->id}", ['name' => 'New Name']);

        $response->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_update_client(): void
    {
        $client = Client::factory()->create(['name' => 'Old Name']);
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->putJson("/api/clients/{$client->id}", [
            'name' => 'New Name',
            'is_active' => false,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Client updated successfully.',
                'data' => [
                    'id' => $client->id,
                    'name' => 'New Name',
                    'is_active' => false,
                ],
            ]);
    }

    public function test_atendente_can_update_client(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $response = $this->withToken($token)->putJson("/api/clients/{$client->id}", [
            'name' => 'Updated By Atendente',
        ]);

        $response->assertOk();
    }

    public function test_update_ignores_own_unique_fields(): void
    {
        $client = Client::factory()->create([
            'email' => 'original@example.com',
            'cpf_cnpj' => '12.345.678/0001-99',
        ]);
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->putJson("/api/clients/{$client->id}", [
            'email' => 'original@example.com',
            'cpf_cnpj' => '12.345.678/0001-99',
        ]);

        $response->assertOk();
    }

    public function test_update_returns_404_for_unknown_client(): void
    {
        $token = $this->loginAs(UserRole::Admin);
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->withToken($token)->putJson("/api/clients/{$nonExistentId}", [
            'name' => 'Ghost',
        ]);

        $response->assertStatus(HttpStatus::NOT_FOUND);
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function test_guest_cannot_list_clients(): void
    {
        $response = $this->getJson('/api/clients');

        $response->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_list_clients(): void
    {
        Client::factory()->count(3)->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->getJson('/api/clients');

        $response
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_atendente_can_list_clients(): void
    {
        $token = $this->loginAs(UserRole::Atendente);

        $response = $this->withToken($token)->getJson('/api/clients');

        $response->assertOk();
    }

    public function test_list_can_filter_by_is_active(): void
    {
        Client::factory()->count(2)->create(['is_active' => true]);
        Client::factory()->count(1)->inactive()->create();
        $token = $this->loginAs(UserRole::Admin);

        $activeResponse = $this->withToken($token)->getJson('/api/clients?is_active=1');
        $inactiveResponse = $this->withToken($token)->getJson('/api/clients?is_active=0');

        $this->assertCount(2, $activeResponse->json('data'));
        $this->assertCount(1, $inactiveResponse->json('data'));
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_guest_cannot_show_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->getJson("/api/clients/{$client->id}");

        $response->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_show_client(): void
    {
        $client = Client::factory()->create(['name' => 'Visible Corp']);
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->getJson("/api/clients/{$client->id}");

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $client->id,
                    'name' => 'Visible Corp',
                ],
            ]);
    }

    public function test_show_returns_404_for_unknown_client(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->getJson('/api/clients/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(HttpStatus::NOT_FOUND);
    }

    // -------------------------------------------------------------------------
    // Deactivate
    // -------------------------------------------------------------------------

    public function test_guest_cannot_deactivate_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_deactivate_client(): void
    {
        $client = Client::factory()->create(['is_active' => true]);
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->deleteJson("/api/clients/{$client->id}");

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Client deactivated successfully.',
                'data' => [
                    'id' => $client->id,
                    'is_active' => false,
                ],
            ]);

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'is_active' => false]);
    }

    public function test_atendente_cannot_deactivate_client(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $response = $this->withToken($token)->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_deactivate_returns_404_for_unknown_client(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->deleteJson('/api/clients/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(HttpStatus::NOT_FOUND);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    private function clientPayload(): array
    {
        return [
            'name' => 'Acme Corp',
            'email' => 'contato@acme.com',
            'cpf_cnpj' => '12.345.678/0001-99',
            'phone' => '(11) 99999-9999',
            'street' => 'Rua das Flores',
            'number' => '123',
            'state' => 'SP',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'zip_code' => '01310-100',
        ];
    }

    private function loginAs(UserRole $role): string
    {
        $user = User::factory()->create([
            'role' => $role->value,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        return $response->json('data.access_token');
    }
}
