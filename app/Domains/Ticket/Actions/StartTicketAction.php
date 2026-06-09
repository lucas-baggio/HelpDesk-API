<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class StartTicketAction
{
    public function execute(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket): Ticket {
            if (! $ticket->status->isOpen()) {
                throw BusinessRuleException::withCode(
                    'TICKET_CANNOT_START',
                    'Only open tickets can be moved to in progress.',
                );
            }

            $ticket->update(['status' => TicketStatus::EmAndamento->value]);

            return $ticket->fresh();
        });
    }
}
