<?php

namespace Tests\Feature\Domains\WorkOrder;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkOrderApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_guest_cannot_create_work_order(): void
    {
        $ticket = Ticket::factory()->create();

        $this->postJson('/api/work-orders', $this->payload($ticket->id))
            ->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_create_work_order(): void
    {
        $ticket = Ticket::factory()->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)
            ->postJson('/api/work-orders', $this->payload($ticket->id));

        $response
            ->assertStatus(HttpStatus::CREATED)
            ->assertJson([
                'success' => true,
                'data' => ['status' => WorkOrderStatus::Aberta->value],
            ]);

        $this->assertStringStartsWith('OS-', $response->json('data.number'));
    }

    public function test_tecnico_can_create_work_order(): void
    {
        $ticket = Ticket::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson('/api/work-orders', $this->payload($ticket->id))
            ->assertStatus(HttpStatus::CREATED);
    }

    public function test_atendente_cannot_create_work_order(): void
    {
        $ticket = Ticket::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $this->withToken($token)
            ->postJson('/api/work-orders', $this->payload($ticket->id))
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_cannot_create_duplicate_work_order_for_same_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        WorkOrder::factory()->forTicket($ticket)->create();
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->postJson('/api/work-orders', $this->payload($ticket->id))
            ->assertStatus(HttpStatus::CONFLICT)
            ->assertJson(['success' => false]);
    }

    public function test_cannot_create_work_order_for_cancelled_ticket(): void
    {
        $ticket = Ticket::factory()->cancelled()->create();
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->postJson('/api/work-orders', $this->payload($ticket->id))
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public function test_create_validation_requires_fields(): void
    {
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->postJson('/api/work-orders', [])
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['errors' => ['ticket_id', 'description']]);
    }

    // -------------------------------------------------------------------------
    // List / Show
    // -------------------------------------------------------------------------

    public function test_all_roles_can_list_work_orders(): void
    {
        WorkOrder::factory()->count(3)->create();

        foreach ([UserRole::Admin, UserRole::Tecnico, UserRole::Atendente] as $role) {
            $this->withToken($this->loginAs($role))
                ->getJson('/api/work-orders')
                ->assertOk()
                ->assertJsonStructure(['data', 'meta']);
        }
    }

    public function test_list_can_filter_by_status(): void
    {
        WorkOrder::factory()->count(2)->create();
        WorkOrder::factory()->inProgress()->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)
            ->getJson('/api/work-orders?status=em_execucao');

        $this->assertCount(1, $response->json('data'));
    }

    public function test_show_returns_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create(['number' => 'OS-00099']);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->getJson("/api/work-orders/{$workOrder->id}")
            ->assertOk()
            ->assertJson(['data' => ['id' => $workOrder->id, 'number' => 'OS-00099']]);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_admin_can_update_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create(['description' => 'Antiga']);
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->putJson("/api/work-orders/{$workOrder->id}", [
                'description' => 'Substituição completa do HD.',
                'service_value' => 450.00,
            ])
            ->assertOk()
            ->assertJson(['data' => ['description' => 'Substituição completa do HD.']]);
    }

    public function test_cannot_update_finalized_work_order(): void
    {
        $workOrder = WorkOrder::factory()->finalized()->create();
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)
            ->putJson("/api/work-orders/{$workOrder->id}", ['description' => 'Hack'])
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public function test_atendente_cannot_update_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $this->withToken($token)
            ->putJson("/api/work-orders/{$workOrder->id}", ['description' => 'Tentativa'])
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    // -------------------------------------------------------------------------
    // Status transitions
    // -------------------------------------------------------------------------

    public function test_tecnico_can_start_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/work-orders/{$workOrder->id}/start")
            ->assertOk()
            ->assertJson(['data' => ['status' => WorkOrderStatus::EmExecucao->value]]);
    }

    public function test_atendente_cannot_start_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $this->withToken($token)
            ->postJson("/api/work-orders/{$workOrder->id}/start")
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_tecnico_can_finalize_work_order(): void
    {
        $workOrder = WorkOrder::factory()->inProgress()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/work-orders/{$workOrder->id}/finalize")
            ->assertOk()
            ->assertJson(['data' => ['status' => WorkOrderStatus::Finalizada->value]]);
    }

    public function test_cannot_finalize_open_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/work-orders/{$workOrder->id}/finalize")
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    public function test_cannot_finalize_already_finalized_work_order(): void
    {
        $workOrder = WorkOrder::factory()->finalized()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/work-orders/{$workOrder->id}/finalize")
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    // RN-024: finalized cannot go back to open/execution
    public function test_cannot_start_finalized_work_order(): void
    {
        $workOrder = WorkOrder::factory()->finalized()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->postJson("/api/work-orders/{$workOrder->id}/start")
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    private function payload(string $ticketId): array
    {
        return [
            'ticket_id' => $ticketId,
            'description' => 'Manutenção preventiva realizada.',
            'service_value' => 200.00,
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
