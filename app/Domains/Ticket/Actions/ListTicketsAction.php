<?php

namespace App\Domains\Ticket\Actions;

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
        ?string $status = null,
        ?string $priority = null,
        int $perPage = self::PER_PAGE,
    ): LengthAwarePaginator {
        $query = Ticket::query()->orderByDesc('created_at');

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($priority !== null) {
            $query->where('priority', $priority);
        }

        return $query->paginate($perPage);
    }
}
