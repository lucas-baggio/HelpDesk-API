<?php

namespace App\Domains\History\Observers;

use App\Domains\History\Actions\RecordHistoryAction;
use App\Domains\History\DTOs\RecordHistoryData;
use App\Domains\History\Enums\HistoryAction;
use App\Domains\History\Enums\HistoryEntityType;
use App\Domains\Ticket\Models\Ticket;

class TicketObserver
{
    public function __construct(private readonly RecordHistoryAction $action) {}

    /** RN-030, RN-031 */
    public function created(Ticket $ticket): void
    {
        $userId = auth()->id();

        if ($userId === null) {
            return;
        }

        $this->action->execute(new RecordHistoryData(
            userId: $userId,
            entityType: HistoryEntityType::Ticket,
            entityId: $ticket->id,
            action: HistoryAction::TicketCreated,
            description: "Ticket \"{$ticket->title}\" created with priority {$ticket->priority->value}.",
        ));
    }

    /** RN-032: status changes must be traceable. */
    public function updated(Ticket $ticket): void
    {
        $userId = auth()->id();

        if ($userId === null) {
            return;
        }

        if ($ticket->wasChanged('status')) {
            $old = $ticket->getRawOriginal('status');
            $new = $ticket->status->value;

            $this->action->execute(new RecordHistoryData(
                userId: $userId,
                entityType: HistoryEntityType::Ticket,
                entityId: $ticket->id,
                action: HistoryAction::TicketStatusChanged,
                description: "Ticket status changed from \"{$old}\" to \"{$new}\".",
            ));
        }
    }
}
