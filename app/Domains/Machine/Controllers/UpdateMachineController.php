<?php

namespace App\Domains\Machine\Controllers;

use App\Domains\Machine\Actions\UpdateMachineAction;
use App\Domains\Machine\Models\Machine;
use App\Domains\Machine\Requests\UpdateMachineRequest;
use App\Domains\Machine\Resources\MachineResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateMachineController extends ApiController
{
    public function __construct(private readonly UpdateMachineAction $action) {}

    public function __invoke(UpdateMachineRequest $request, Machine $machine): JsonResponse
    {
        $machine = $this->action->execute($machine, $request->toUpdateMachineData());

        return ApiResponse::success(
            data: new MachineResource($machine),
            message: 'Machine updated successfully.',
        );
    }
}
