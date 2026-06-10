<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTicketsAction
{
    private const PER_PAGE = 15;

    /**
     * @return LengthAwarePaginator<Ticket>
     */
    public function execute(
        ?string $clientId = null,
        ?TicketStatus $status = null,
        ?TicketPriority $priority = null,
        int $perPage = self::PER_PAGE,
    ): LengthAwarePaginator {
        $query = Ticket::query()->orderByDesc('created_at');

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        if ($priority !== null) {
            $query->where('priority', $priority->value);
        }

        return $query->paginate($perPage);
    }
}
