<?php

namespace App\Domains\Client\Controllers;

use App\Domains\Client\Actions\ShowClientAction;
use App\Domains\Client\Models\Client;
use App\Domains\Client\Resources\ClientResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ShowClientController extends ApiController
{
    public function __construct(private readonly ShowClientAction $action) {}

    public function __invoke(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return ApiResponse::success(
            data: new ClientResource($this->action->execute($client)),
            message: 'Client retrieved successfully.',
        );
    }
}
