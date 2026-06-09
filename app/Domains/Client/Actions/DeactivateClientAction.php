<?php

namespace App\Domains\Client\Actions;

use App\Domains\Client\Models\Client;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class DeactivateClientAction
{
    public function execute(Client $client): Client
    {
        return DB::transaction(function () use ($client): Client {
            // RN-007: block deactivation when machines or tickets exist.
            // TODO: uncomment once Machine and Ticket domains are implemented.
            // if ($client->machines()->exists() || $client->tickets()->exists()) {
            //     throw BusinessRuleException::withCode(
            //         'CLIENT_HAS_DEPENDENCIES',
            //         'Client cannot be deactivated while machines or tickets are linked to it.',
            //     );
            // }

            $client->update(['is_active' => false]);

            return $client->fresh();
        });
    }
}
