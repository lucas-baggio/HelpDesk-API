<?php

namespace Tests\Feature\Domains\Ticket;

use App\Domains\Client\Models\Client;
use App\Domains\Machine\Models\Machine;
use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_guest_cannot_create_ticket(): void
    {
        $client = Client::factory()->create();

        $this->postJson('/api/tickets', $this->ticketPayload($client->id))
            ->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_create_ticket(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->postJson('/api/tickets', $this->ticketPayload($client->id));

        $response
            ->assertStatus(HttpStatus::CREATED)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => TicketStatus::Aberto->value,
                    'priority' => TicketPriority::Alta->value,
                ],
            ]);
    }

    public function test_atendente_can_create_ticket(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $this->withToken($token)
            ->postJson('/api/tickets', $this->ticketPayload($client->id))
            ->assertStatus(HttpStatus::CREATED);
    }

    public function test_tecnico_cannot_create_ticket(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson('/api/tickets', $this->ticketPayload($client->id))
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_create_ticket_with_machine(): void
    {
        $client = Client::factory()->create();
        $machine = Machine::factory()->forClient($client)->create();
        $token = $this->loginAs(UserRole::Admin);

        $payload = $this->ticketPayload($client->id);
        $payload['machine_id'] = $machine->id;

        $this->withToken($token)
            ->postJson('/api/tickets', $payload)
            ->assertStatus(HttpStatus::CREATED)
            ->assertJson(['data' => ['machine_id' => $machine->id]]);
    }

    public function test_create_rejects_machine_from_different_client(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $machine = Machine::factory()->forClient($clientB)->create();
        $token = $this->loginAs(UserRole::Admin);

        $payload = $this->ticketPayload($clientA->id);
        $payload['machine_id'] = $machine->id;

        $this->withToken($token)
            ->postJson('/api/tickets', $payload)
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJson(['success' => false]);
    }

    public function test_create_validation_returns_standard_envelope(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->postJson('/api/tickets', [])
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['errors' => ['client_id', 'title', 'description', 'priority']]);
    }

    // -------------------------------------------------------------------------
    // List / Show
    // -------------------------------------------------------------------------

    public function test_all_roles_can_list_tickets(): void
    {
        Ticket::factory()->count(3)->create();

        foreach ([UserRole::Admin, UserRole::Atendente, UserRole::Tecnico] as $role) {
            $this->withToken($this->loginAs($role))
                ->getJson('/api/tickets')
                ->assertOk()
                ->assertJsonStructure(['data', 'meta']);
        }
    }

    public function test_list_can_filter_by_status(): void
    {
        Ticket::factory()->count(2)->create();
        Ticket::factory()->inProgress()->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)
            ->getJson('/api/tickets?status=em_andamento');

        $this->assertCount(1, $response->json('data'));
    }

    public function test_show_returns_ticket(): void
    {
        $ticket = Ticket::factory()->create(['title' => 'Meu Chamado']);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->getJson("/api/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJson(['data' => ['id' => $ticket->id, 'title' => 'Meu Chamado']]);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_admin_can_update_ticket(): void
    {
        $ticket = Ticket::factory()->create(['title' => 'Antigo']);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->putJson("/api/tickets/{$ticket->id}", ['title' => 'Novo', 'priority' => 'baixa'])
            ->assertOk()
            ->assertJson(['data' => ['title' => 'Novo', 'priority' => 'baixa']]);
    }

    public function test_cannot_update_closed_ticket(): void
    {
        $ticket = Ticket::factory()->resolved()->create();
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->putJson("/api/tickets/{$ticket->id}", ['title' => 'Hack'])
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    // -------------------------------------------------------------------------
    // Status transitions
    // -------------------------------------------------------------------------

    public function test_tecnico_can_start_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/tickets/{$ticket->id}/start")
            ->assertOk()
            ->assertJson(['data' => ['status' => TicketStatus::EmAndamento->value]]);
    }

    public function test_atendente_cannot_start_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $this->withToken($token)
            ->postJson("/api/tickets/{$ticket->id}/start")
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_tecnico_can_resolve_ticket(): void
    {
        $ticket = Ticket::factory()->inProgress()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $response = $this->withToken($token)
            ->postJson("/api/tickets/{$ticket->id}/resolve");

        $response
            ->assertOk()
            ->assertJson(['data' => ['status' => TicketStatus::Resolvido->value]]);

        $this->assertNotNull($response->json('data.resolved_at'));
        $this->assertNotNull($response->json('data.resolved_by'));
    }

    public function test_cancelled_ticket_cannot_be_resolved(): void
    {
        $ticket = Ticket::factory()->cancelled()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/tickets/{$ticket->id}/resolve")
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJson(['success' => false]);
    }

    public function test_tecnico_can_cancel_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/tickets/{$ticket->id}/cancel")
            ->assertOk()
            ->assertJson(['data' => ['status' => TicketStatus::Cancelado->value]]);
    }

    public function test_resolved_ticket_cannot_be_cancelled(): void
    {
        $ticket = Ticket::factory()->resolved()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/tickets/{$ticket->id}/cancel")
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    private function ticketPayload(string $clientId): array
    {
        return [
            'client_id' => $clientId,
            'title' => 'Impressora não funciona',
            'description' => 'A impressora parou de funcionar após atualização.',
            'priority' => TicketPriority::Alta->value,
        ];
    }

    private function loginAs(UserRole $role): string
    {
        $user = User::factory()->create([
            'role' => $role->value,
            'password' => Hash::make('password123'),
        ]);

        return $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->json('data.access_token');
    }
}
