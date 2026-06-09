<?php

namespace Tests\Unit\Domains\Ticket\Actions;

use App\Domains\Ticket\Actions\CancelTicketAction;
use App\Domains\Ticket\Actions\ResolveTicketAction;
use App\Domains\Ticket\Actions\StartTicketAction;
use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Models\User;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketStatusActionsTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // StartTicketAction
    // -------------------------------------------------------------------------

    public function test_start_moves_open_ticket_to_in_progress(): void
    {
        $ticket = Ticket::factory()->create();

        $updated = (new StartTicketAction())->execute($ticket);

        $this->assertSame(TicketStatus::EmAndamento, $updated->status);
    }

    public function test_start_throws_when_ticket_is_not_open(): void
    {
        $ticket = Ticket::factory()->inProgress()->create();

        $this->expectException(BusinessRuleException::class);

        (new StartTicketAction())->execute($ticket);
    }

    // -------------------------------------------------------------------------
    // ResolveTicketAction
    // -------------------------------------------------------------------------

    public function test_resolve_sets_status_resolved_at_and_resolved_by(): void
    {
        $ticket = Ticket::factory()->inProgress()->create();
        $resolver = User::factory()->create();

        $updated = (new ResolveTicketAction())->execute($ticket, $resolver);

        $this->assertSame(TicketStatus::Resolvido, $updated->status);
        $this->assertSame($resolver->id, $updated->resolved_by);
        $this->assertNotNull($updated->resolved_at);
    }

    public function test_resolve_throws_for_cancelled_ticket(): void
    {
        $ticket = Ticket::factory()->cancelled()->create();
        $resolver = User::factory()->create();

        $this->expectException(BusinessRuleException::class);

        (new ResolveTicketAction())->execute($ticket, $resolver);
    }

    public function test_resolve_throws_when_already_resolved(): void
    {
        $ticket = Ticket::factory()->resolved()->create();
        $resolver = User::factory()->create();

        $this->expectException(BusinessRuleException::class);

        (new ResolveTicketAction())->execute($ticket, $resolver);
    }

    // -------------------------------------------------------------------------
    // CancelTicketAction
    // -------------------------------------------------------------------------

    public function test_cancel_moves_ticket_to_cancelled(): void
    {
        $ticket = Ticket::factory()->create();

        $updated = (new CancelTicketAction())->execute($ticket);

        $this->assertSame(TicketStatus::Cancelado, $updated->status);
    }

    public function test_cancel_throws_when_ticket_is_already_closed(): void
    {
        $ticket = Ticket::factory()->resolved()->create();

        $this->expectException(BusinessRuleException::class);

        (new CancelTicketAction())->execute($ticket);
    }
}
