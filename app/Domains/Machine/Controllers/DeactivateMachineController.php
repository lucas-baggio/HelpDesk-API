<?php

namespace App\Domains\Machine\Controllers;

use App\Domains\Machine\Actions\DeactivateMachineAction;
use App\Domains\Machine\Models\Machine;
use App\Domains\Machine\Resources\MachineResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class DeactivateMachineController extends ApiController
{
    public function __construct(private readonly DeactivateMachineAction $action) {}

    public function __invoke(Machine $machine): JsonResponse
    {
        $this->authorize('delete', $machine);

        $machine = $this->action->execute($machine);

        return ApiResponse::success(
            data: new MachineResource($machine),
            message: 'Machine deactivated successfully.',
        );
    }
}
