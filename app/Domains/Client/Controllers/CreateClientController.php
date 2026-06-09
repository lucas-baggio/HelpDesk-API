<?php

namespace App\Domains\Client\Controllers;

use App\Domains\Client\Actions\CreateClientAction;
use App\Domains\Client\Requests\CreateClientRequest;
use App\Domains\Client\Resources\ClientResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateClientController extends ApiController
{
    public function __construct(private readonly CreateClientAction $action) {}

    public function __invoke(CreateClientRequest $request): JsonResponse
    {
        $client = $this->action->execute($request->toCreateClientData());

        return ApiResponse::created(
            data: new ClientResource($client),
            message: 'Client created successfully.',
        );
    }
}
