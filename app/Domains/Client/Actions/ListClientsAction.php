<?php

namespace App\Domains\Client\Actions;

use App\Domains\Client\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListClientsAction
{
    private const PER_PAGE = 15;

    /**
     * @return LengthAwarePaginator<Client>
     */
    public function execute(?bool $isActive = null, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $query = Client::query()->orderBy('name');

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->paginate($perPage);
    }
}
