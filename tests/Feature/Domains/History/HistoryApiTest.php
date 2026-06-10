<?php

namespace Tests\Feature\Domains\History;

use App\Domains\Client\Models\Client;
use App\Domains\History\Enums\HistoryAction;
use App\Domains\History\Enums\HistoryEntityType;
use App\Domains\History\Models\History;
use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HistoryApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Auto-recording via observers
    // -------------------------------------------------------------------------

    public function test_creating_ticket_records_history(): void
    {
        $client = Client::factory()->create();
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)->postJson('/api/tickets', [
            'client_id' => $client->id,
            'title' => 'Servidor fora',
            'description' => 'Servidor inacessível.',
            'priority' => TicketPriority::Alta->value,
        ]);

        $this->assertDatabaseHas('histories', [
            'entity_type' => HistoryEntityType::Ticket->value,
            'action' => HistoryAction::TicketCreated->value,
        ]);
    }

    public function test_changing_ticket_status_records_history(): void
    {
        $ticket = Ticket::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)->postJson("/api/tickets/{$ticket->id}/start");

        $this->assertDatabaseHas('histories', [
            'entity_type' => HistoryEntityType::Ticket->value,
            'entity_id' => $ticket->id,
            'action' => HistoryAction::TicketStatusChanged->value,
        ]);
    }

    public function test_resolving_ticket_records_history(): void
    {
        $ticket = Ticket::factory()->inProgress()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)->postJson("/api/tickets/{$ticket->id}/resolve");

        $this->assertDatabaseHas('histories', [
            'entity_type' => HistoryEntityType::Ticket->value,
            'entity_id' => $ticket->id,
            'action' => HistoryAction::TicketStatusChanged->value,
        ]);
    }

    public function test_creating_work_order_records_history(): void
    {
        $ticket = Ticket::factory()->create();
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)->postJson('/api/work-orders', [
            'ticket_id' => $ticket->id,
            'description' => 'Manutenção preventiva.',
            'service_value' => 300.00,
        ]);

        $this->assertDatabaseHas('histories', [
            'entity_type' => HistoryEntityType::WorkOrder->value,
            'action' => HistoryAction::WorkOrderCreated->value,
        ]);
    }

    public function test_changing_work_order_status_records_history(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)->postJson("/api/work-orders/{$workOrder->id}/start");

        $this->assertDatabaseHas('histories', [
            'entity_type' => HistoryEntityType::WorkOrder->value,
            'entity_id' => $workOrder->id,
            'action' => HistoryAction::WorkOrderStatusChanged->value,
        ]);
    }

    public function test_updating_service_value_records_history(): void
    {
        $workOrder = WorkOrder::factory()->create(['service_value' => 100.00]);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)->putJson("/api/work-orders/{$workOrder->id}", [
            'service_value' => 999.99,
        ]);

        $this->assertDatabaseHas('histories', [
            'entity_type' => HistoryEntityType::WorkOrder->value,
            'entity_id' => $workOrder->id,
            'action' => HistoryAction::WorkOrderServiceValueUpdated->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /histories
    // -------------------------------------------------------------------------

    public function test_all_roles_can_list_histories(): void
    {
        History::factory()->count(3)->create();

        foreach ([UserRole::Admin, UserRole::Tecnico, UserRole::Atendente] as $role) {
            $this->withToken($this->loginAs($role))
                ->getJson('/api/histories')
                ->assertOk()
                ->assertJsonStructure(['data', 'meta']);
        }
    }

    public function test_guest_cannot_list_histories(): void
    {
        $this->getJson('/api/histories')
            ->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_list_filters_by_entity_type(): void
    {
        History::factory()->forTicket(fake()->uuid())->count(2)->create();
        History::factory()->forWorkOrder(fake()->uuid())->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)
            ->getJson('/api/histories?entity_type=ticket');

        $this->assertCount(2, $response->json('data'));
    }

    public function test_list_filters_by_entity_id(): void
    {
        $ticketId = fake()->uuid();
        History::factory()->forTicket($ticketId)->count(3)->create();
        History::factory()->forTicket(fake()->uuid())->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)
            ->getJson("/api/histories?entity_id={$ticketId}");

        $this->assertCount(3, $response->json('data'));
    }

    public function test_show_returns_history_entry(): void
    {
        $history = History::factory()->create(['action' => HistoryAction::TicketCreated->value]);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->getJson("/api/histories/{$history->id}")
            ->assertOk()
            ->assertJson(['data' => [
                'id' => $history->id,
                'action' => HistoryAction::TicketCreated->value,
            ]]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

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
