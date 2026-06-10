<?php

namespace Tests\Unit\Domains\WorkOrder\Actions;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\WorkOrder\Actions\CreateWorkOrderAction;
use App\Domains\WorkOrder\DTOs\CreateWorkOrderData;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Exceptions\BusinessRuleException;
use App\Shared\Exceptions\ResourceConflictException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateWorkOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateWorkOrderAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateWorkOrderAction();
    }

    public function test_creates_work_order_with_status_aberta(): void
    {
        $ticket = Ticket::factory()->create();

        $data = new CreateWorkOrderData(
            ticketId: $ticket->id,
            description: 'Substituição do cartucho.',
            serviceValue: 150.00,
        );

        $workOrder = $this->action->execute($data);

        $this->assertInstanceOf(WorkOrder::class, $workOrder);
        $this->assertSame(WorkOrderStatus::Aberta, $workOrder->status);
        $this->assertStringStartsWith('OS-', $workOrder->number);
        $this->assertDatabaseHas('work_orders', [
            'ticket_id' => $ticket->id,
            'status' => WorkOrderStatus::Aberta->value,
        ]);
    }

    public function test_number_is_auto_incremented(): void
    {
        $first = $this->action->execute(new CreateWorkOrderData(
            ticketId: Ticket::factory()->create()->id,
            description: 'Primeira OS.',
        ));

        $second = $this->action->execute(new CreateWorkOrderData(
            ticketId: Ticket::factory()->create()->id,
            description: 'Segunda OS.',
        ));

        $this->assertSame('OS-00001', $first->number);
        $this->assertSame('OS-00002', $second->number);
    }

    public function test_throws_when_ticket_already_has_work_order(): void
    {
        $ticket = Ticket::factory()->create();
        WorkOrder::factory()->forTicket($ticket)->create();

        $this->expectException(ResourceConflictException::class);

        $this->action->execute(new CreateWorkOrderData(
            ticketId: $ticket->id,
            description: 'Segunda OS tentando ser criada.',
        ));
    }

    public function test_throws_when_ticket_is_cancelled(): void
    {
        $ticket = Ticket::factory()->cancelled()->create();

        $this->expectException(BusinessRuleException::class);

        $this->action->execute(new CreateWorkOrderData(
            ticketId: $ticket->id,
            description: 'OS para ticket cancelado.',
        ));
    }
}
