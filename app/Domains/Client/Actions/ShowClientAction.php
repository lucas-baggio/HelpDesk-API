<?php

namespace App\Domains\Client\Actions;

use App\Domains\Client\Models\Client;

class ShowClientAction
{
    public function execute(Client $client): Client
    {
        return $client;
    }
}
