<?php

namespace App\Domains\Machine\Actions;

use App\Domains\Machine\Models\Machine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListMachinesAction
{
    private const PER_PAGE = 15;

    /**
     * @return LengthAwarePaginator<Machine>
     */
    public function execute(
        ?string $clientId = null,
        ?bool $isActive = null,
        int $perPage = self::PER_PAGE,
    ): LengthAwarePaginator {
        $query = Machine::query()->orderBy('name');

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->paginate($perPage);
    }
}
