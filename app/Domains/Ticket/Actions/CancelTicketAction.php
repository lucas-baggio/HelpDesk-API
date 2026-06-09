<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class CancelTicketAction
{
    public function execute(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket): Ticket {
            if ($ticket->status->isClosed()) {
                throw BusinessRuleException::withCode(
                    'TICKET_ALREADY_CLOSED',
                    'This ticket is already closed.',
                );
            }

            $ticket->update(['status' => TicketStatus::Cancelado->value]);

            return $ticket->fresh();
        });
    }
}
