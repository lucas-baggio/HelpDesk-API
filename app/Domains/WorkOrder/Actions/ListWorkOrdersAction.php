<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListWorkOrdersAction
{
    private const PER_PAGE = 15;

    /**
     * @return LengthAwarePaginator<WorkOrder>
     */
    public function execute(
        ?string $ticketId = null,
        ?WorkOrderStatus $status = null,
        int $perPage = self::PER_PAGE,
    ): LengthAwarePaginator {
        $query = WorkOrder::query()->orderByDesc('created_at');

        if ($ticketId !== null) {
            $query->where('ticket_id', $ticketId);
        }

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        return $query->paginate($perPage);
    }
}
