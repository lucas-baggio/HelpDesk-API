<?php

namespace Tests\Unit\Domains\Ticket\Actions;

use App\Domains\Client\Models\Client;
use App\Domains\Machine\Models\Machine;
use App\Domains\Ticket\Actions\CreateTicketAction;
use App\Domains\Ticket\DTOs\CreateTicketData;
use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Models\User;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTicketActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateTicketAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateTicketAction();
    }

    public function test_creates_ticket_with_status_aberto(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create();

        $data = new CreateTicketData(
            clientId: $client->id,
            createdBy: $user->id,
            title: 'Impressora não funciona',
            description: 'A impressora parou de funcionar.',
            priority: TicketPriority::Alta,
        );

        $ticket = $this->action->execute($data);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertSame(TicketStatus::Aberto, $ticket->status);
        $this->assertNull($ticket->machine_id);
        $this->assertDatabaseHas('tickets', [
            'client_id' => $client->id,
            'created_by' => $user->id,
            'status' => TicketStatus::Aberto->value,
        ]);
    }

    public function test_creates_ticket_with_machine(): void
    {
        $client = Client::factory()->create();
        $machine = Machine::factory()->forClient($client)->create();
        $user = User::factory()->create();

        $data = new CreateTicketData(
            clientId: $client->id,
            createdBy: $user->id,
            title: 'Erro na máquina',
            description: 'Detalhe do erro.',
            priority: TicketPriority::Media,
            machineId: $machine->id,
        );

        $ticket = $this->action->execute($data);

        $this->assertSame($machine->id, $ticket->machine_id);
    }

    public function test_throws_when_machine_belongs_to_different_client(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $machine = Machine::factory()->forClient($clientB)->create();
        $user = User::factory()->create();

        $this->expectException(BusinessRuleException::class);

        $this->action->execute(new CreateTicketData(
            clientId: $clientA->id,
            createdBy: $user->id,
            title: 'Teste',
            description: 'Desc.',
            priority: TicketPriority::Baixa,
            machineId: $machine->id,
        ));
    }
}
