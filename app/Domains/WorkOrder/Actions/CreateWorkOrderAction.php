<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\WorkOrder\DTOs\CreateWorkOrderData;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Exceptions\BusinessRuleException;
use App\Shared\Exceptions\ResourceConflictException;
use Illuminate\Support\Facades\DB;

class CreateWorkOrderAction
{
    public function execute(CreateWorkOrderData $data): WorkOrder
    {
        return DB::transaction(function () use ($data): WorkOrder {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($data->ticketId);

            // RN-022: ticket must not be cancelled
            if ($ticket->status->isCancelled()) {
                throw BusinessRuleException::withCode(
                    'WORK_ORDER_TICKET_CANCELLED',
                    'Cannot create a work order for a cancelled ticket.',
                );
            }

            // RN-021: only one work order per ticket
            if ($ticket->workOrder()->exists()) {
                throw ResourceConflictException::withCode(
                    'WORK_ORDER_ALREADY_EXISTS',
                    'This ticket already has a work order.',
                );
            }

            $number = $this->generateNumber();

            return WorkOrder::query()->create([
                'ticket_id' => $data->ticketId,
                'number' => $number,
                'description' => $data->description,
                'service_value' => $data->serviceValue,
                'status' => WorkOrderStatus::Aberta->value,
            ]);
        });
    }

    private function generateNumber(): string
    {
        $last = WorkOrder::query()
            ->lockForUpdate()
            ->orderByDesc('number')
            ->value('number');

        $next = $last
            ? (int) substr($last, 3) + 1
            : 1;

        return 'OS-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
