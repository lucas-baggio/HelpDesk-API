<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\DTOs\UpdateTicketData;
use App\Domains\Ticket\Models\Ticket;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class UpdateTicketAction
{
    public function execute(Ticket $ticket, UpdateTicketData $data): Ticket
    {
        return DB::transaction(function () use ($ticket, $data): Ticket {
            if ($ticket->status->isClosed()) {
                throw BusinessRuleException::withCode(
                    'TICKET_ALREADY_CLOSED',
                    'A closed ticket cannot be edited.',
                );
            }

            // Validate machine belongs to same client when changing it
            if ($data->machineId !== null) {
                $valid = \App\Domains\Machine\Models\Machine::query()
                    ->where('id', $data->machineId)
                    ->where('client_id', $ticket->client_id)
                    ->exists();

                if (! $valid) {
                    throw BusinessRuleException::withCode(
                        'TICKET_MACHINE_CLIENT_MISMATCH',
                        'The machine does not belong to this ticket\'s client.',
                    );
                }
            }

            $ticket->fill($data->toPersistenceArray());
            $ticket->save();

            return $ticket->fresh();
        });
    }
}
