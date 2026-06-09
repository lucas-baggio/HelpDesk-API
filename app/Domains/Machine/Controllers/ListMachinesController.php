<?php

namespace App\Domains\Machine\Controllers;

use App\Domains\Machine\Actions\ListMachinesAction;
use App\Domains\Machine\Models\Machine;
use App\Domains\Machine\Resources\MachineResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMachinesController extends ApiController
{
    public function __construct(private readonly ListMachinesAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Machine::class);

        $clientId = $request->query('client_id');
        $isActive = match ($request->query('is_active')) {
            '1', 'true' => true,
            '0', 'false' => false,
            default => null,
        };

        $machines = $this->action->execute(
            clientId: is_string($clientId) ? $clientId : null,
            isActive: $isActive,
        );

        return ApiResponse::paginated(
            collection: MachineResource::collection($machines),
            message: 'Machines retrieved successfully.',
        );
    }
}
