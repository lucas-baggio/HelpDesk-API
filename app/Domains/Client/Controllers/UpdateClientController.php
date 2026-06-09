<?php

namespace App\Domains\Client\Controllers;

use App\Domains\Client\Actions\UpdateClientAction;
use App\Domains\Client\Models\Client;
use App\Domains\Client\Requests\UpdateClientRequest;
use App\Domains\Client\Resources\ClientResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateClientController extends ApiController
{
    public function __construct(private readonly UpdateClientAction $action) {}

    public function __invoke(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client = $this->action->execute($client, $request->toUpdateClientData());

        return ApiResponse::success(
            data: new ClientResource($client),
            message: 'Client updated successfully.',
        );
    }
}
