<?php

namespace App\Domains\Client\Controllers;

use App\Domains\Client\Actions\DeactivateClientAction;
use App\Domains\Client\Models\Client;
use App\Domains\Client\Resources\ClientResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class DeactivateClientController extends ApiController
{
    public function __construct(private readonly DeactivateClientAction $action) {}

    public function __invoke(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $client = $this->action->execute($client);

        return ApiResponse::success(
            data: new ClientResource($client),
            message: 'Client deactivated successfully.',
        );
    }
}
