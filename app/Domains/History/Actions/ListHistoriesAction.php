<?php

namespace App\Domains\History\Actions;

use App\Domains\History\Models\History;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListHistoriesAction
{
    private const PER_PAGE = 20;

    /**
     * @return LengthAwarePaginator<History>
     */
    public function execute(
        ?string $entityType = null,
        ?string $entityId = null,
        ?string $userId = null,
        int $perPage = self::PER_PAGE,
    ): LengthAwarePaginator {
        $query = History::query()->orderByDesc('created_at');

        if ($entityType !== null) {
            $query->where('entity_type', $entityType);
        }

        if ($entityId !== null) {
            $query->where('entity_id', $entityId);
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->paginate($perPage);
    }
}
