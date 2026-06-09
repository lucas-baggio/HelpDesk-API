<?php

namespace App\Domains\Client\Controllers;

use App\Domains\Client\Actions\ListClientsAction;
use App\Domains\Client\Models\Client;
use App\Domains\Client\Resources\ClientResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListClientsController extends ApiController
{
    public function __construct(private readonly ListClientsAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $isActive = match ($request->query('is_active')) {
            '1', 'true' => true,
            '0', 'false' => false,
            default => null,
        };

        $clients = $this->action->execute(isActive: $isActive);

        return ApiResponse::paginated(
            collection: ClientResource::collection($clients),
            message: 'Clients retrieved successfully.',
        );
    }
}
