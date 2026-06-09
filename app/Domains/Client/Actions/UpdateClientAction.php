<?php

namespace App\Domains\Client\Actions;

use App\Domains\Client\DTOs\UpdateClientData;
use App\Domains\Client\Models\Client;
use Illuminate\Support\Facades\DB;

class UpdateClientAction
{
    public function execute(Client $client, UpdateClientData $data): Client
    {
        return DB::transaction(function () use ($client, $data): Client {
            $client->fill($data->toPersistenceArray());
            $client->save();

            return $client->fresh();
        });
    }
}
