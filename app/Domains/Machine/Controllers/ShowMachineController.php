<?php

namespace App\Domains\Machine\Controllers;

use App\Domains\Machine\Actions\ShowMachineAction;
use App\Domains\Machine\Models\Machine;
use App\Domains\Machine\Resources\MachineResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ShowMachineController extends ApiController
{
    public function __construct(private readonly ShowMachineAction $action) {}

    public function __invoke(Machine $machine): JsonResponse
    {
        $this->authorize('view', $machine);

        return ApiResponse::success(
            data: new MachineResource($this->action->execute($machine)),
            message: 'Machine retrieved successfully.',
        );
    }
}
