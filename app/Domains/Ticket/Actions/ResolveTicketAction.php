<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Models\User;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class ResolveTicketAction
{
    public function execute(Ticket $ticket, User $resolvedBy): Ticket
    {
        return DB::transaction(function () use ($ticket, $resolvedBy): Ticket {
            // RN-017: cancelled ticket cannot be resolved
            if ($ticket->status->isCancelled()) {
                throw BusinessRuleException::withCode(
                    'TICKET_CANCELLED_CANNOT_RESOLVE',
                    'A cancelled ticket cannot be resolved.',
                );
            }

            if ($ticket->status->isResolved()) {
                throw BusinessRuleException::withCode(
                    'TICKET_ALREADY_RESOLVED',
                    'This ticket is already resolved.',
                );
            }

            // RN-019: register resolved_at and resolved_by
            $ticket->update([
                'status' => TicketStatus::Resolvido->value,
                'resolved_by' => $resolvedBy->id,
                'resolved_at' => now(),
            ]);

            return $ticket->fresh();
        });
    }
}
