<?php

namespace Tests\Feature\Domains\Machine;

use App\Domains\Client\Models\Client;
use App\Domains\Machine\Models\Machine;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MachineApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function test_guest_cannot_list_machines(): void
    {
        $this->getJson('/api/machines')->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_all_roles_can_list_machines(): void
    {
        Machine::factory()->count(3)->create();

        foreach ([UserRole::Admin, UserRole::Atendente, UserRole::Tecnico] as $role) {
            $token = $this->loginAs($role);

            $this->withToken($token)
                ->getJson('/api/machines')
                ->assertOk()
                ->assertJsonStructure(['success', 'data', 'meta']);
        }
    }

    public function test_list_can_filter_by_client(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        Machine::factory()->count(2)->forClient($clientA)->create();
        Machine::factory()->count(1)->forClient($clientB)->create();

        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->getJson("/api/machines?client_id={$clientA->id}");

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_list_can_filter_by_is_active(): void
    {
        Machine::factory()->count(2)->create(['is_active' => true]);
        Machine::factory()->count(1)->inactive()->create();

        $token = $this->loginAs(UserRole::Admin);

        $active = $this->withToken($token)->getJson('/api/machines?is_active=1');
        $inactive = $this->withToken($token)->getJson('/api/machines?is_active=0');

        $this->assertCount(2, $active->json('data'));
        $this->assertCount(1, $inactive->json('data'));
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_guest_cannot_create_machine(): void
    {
        $client = Client::factory()->create();

        $this->postJson('/api/machines', $this->machinePayload($client->id))
            ->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_create_machine(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->postJson('/api/machines', $this->machinePayload($client->id));

        $response
            ->assertStatus(HttpStatus::CREATED)
            ->assertJson([
                'success' => true,
                'message' => 'Machine created successfully.',
                'data' => [
                    'client_id' => $client->id,
                    'name' => 'Notebook Dell',
                    'is_active' => true,
                ],
            ]);
    }

    public function test_atendente_can_create_machine(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $this->withToken($token)
            ->postJson('/api/machines', $this->machinePayload($client->id))
            ->assertStatus(HttpStatus::CREATED);
    }

    public function test_tecnico_cannot_create_machine(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson('/api/machines', $this->machinePayload($client->id))
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_create_rejects_duplicate_serial_in_same_client(): void
    {
        $client = Client::factory()->create();
        Machine::factory()->forClient($client)->create(['serial_number' => 'SN-DUPE']);
        $token = $this->loginAs(UserRole::Admin);

        $payload = $this->machinePayload($client->id);
        $payload['serial_number'] = 'SN-DUPE';

        $this->withToken($token)
            ->postJson('/api/machines', $payload)
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public function test_create_validation_returns_standard_envelope(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->postJson('/api/machines', [])
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'message', 'errors' => ['client_id', 'name']]);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_guest_cannot_show_machine(): void
    {
        $machine = Machine::factory()->create();

        $this->getJson("/api/machines/{$machine->id}")->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_all_roles_can_show_machine(): void
    {
        $machine = Machine::factory()->create(['name' => 'Visible Machine']);

        foreach ([UserRole::Admin, UserRole::Atendente, UserRole::Tecnico] as $role) {
            $token = $this->loginAs($role);

            $this->withToken($token)
                ->getJson("/api/machines/{$machine->id}")
                ->assertOk()
                ->assertJson(['data' => ['id' => $machine->id]]);
        }
    }

    public function test_show_returns_404_for_unknown_machine(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->getJson('/api/machines/00000000-0000-0000-0000-000000000000')
            ->assertStatus(HttpStatus::NOT_FOUND);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_guest_cannot_update_machine(): void
    {
        $machine = Machine::factory()->create();

        $this->putJson("/api/machines/{$machine->id}", ['name' => 'New'])
            ->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_update_machine(): void
    {
        $machine = Machine::factory()->create(['name' => 'Old']);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->putJson("/api/machines/{$machine->id}", ['name' => 'New', 'is_active' => false])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['id' => $machine->id, 'name' => 'New', 'is_active' => false],
            ]);
    }

    public function test_update_ignores_own_serial_number(): void
    {
        $client = Client::factory()->create();
        $machine = Machine::factory()->forClient($client)->create(['serial_number' => 'MY-SN']);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->putJson("/api/machines/{$machine->id}", ['serial_number' => 'MY-SN'])
            ->assertOk();
    }

    public function test_tecnico_cannot_update_machine(): void
    {
        $machine = Machine::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->putJson("/api/machines/{$machine->id}", ['name' => 'Hack'])
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    // -------------------------------------------------------------------------
    // Deactivate
    // -------------------------------------------------------------------------

    public function test_guest_cannot_deactivate_machine(): void
    {
        $machine = Machine::factory()->create();

        $this->deleteJson("/api/machines/{$machine->id}")->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_deactivate_machine(): void
    {
        $machine = Machine::factory()->create(['is_active' => true]);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->deleteJson("/api/machines/{$machine->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Machine deactivated successfully.',
                'data' => ['id' => $machine->id, 'is_active' => false],
            ]);

        $this->assertDatabaseHas('machines', ['id' => $machine->id, 'is_active' => false]);
    }

    public function test_atendente_cannot_deactivate_machine(): void
    {
        $machine = Machine::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $this->withToken($token)
            ->deleteJson("/api/machines/{$machine->id}")
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_deactivate_returns_404_for_unknown_machine(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->deleteJson('/api/machines/00000000-0000-0000-0000-000000000000')
            ->assertStatus(HttpStatus::NOT_FOUND);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    private function machinePayload(string $clientId): array
    {
        return [
            'client_id' => $clientId,
            'name' => 'Notebook Dell',
            'model' => 'Latitude 5420',
            'serial_number' => 'SN-00001',
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
