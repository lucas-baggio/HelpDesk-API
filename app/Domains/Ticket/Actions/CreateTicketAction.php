<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\DTOs\CreateTicketData;
use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class CreateTicketAction
{
    public function execute(CreateTicketData $data): Ticket
    {
        return DB::transaction(function () use ($data): Ticket {
            // RN-014: machine, when provided, must belong to the same client
            if ($data->machineId !== null) {
                $valid = \App\Domains\Machine\Models\Machine::query()
                    ->where('id', $data->machineId)
                    ->where('client_id', $data->clientId)
                    ->exists();

                if (! $valid) {
                    throw BusinessRuleException::withCode(
                        'TICKET_MACHINE_CLIENT_MISMATCH',
                        'The machine does not belong to the selected client.',
                    );
                }
            }

            return Ticket::query()->create([
                'client_id' => $data->clientId,
                'machine_id' => $data->machineId,
                'created_by' => $data->createdBy,
                'title' => $data->title,
                'description' => $data->description,
                'priority' => $data->priority->value,
                'status' => TicketStatus::Aberto->value,
            ]);
        });
    }
}
