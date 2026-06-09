<?php

namespace App\Domains\Machine\Controllers;

use App\Domains\Machine\Actions\CreateMachineAction;
use App\Domains\Machine\Requests\CreateMachineRequest;
use App\Domains\Machine\Resources\MachineResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateMachineController extends ApiController
{
    public function __construct(private readonly CreateMachineAction $action) {}

    public function __invoke(CreateMachineRequest $request): JsonResponse
    {
        $machine = $this->action->execute($request->toCreateMachineData());

        return ApiResponse::created(
            data: new MachineResource($machine),
            message: 'Machine created successfully.',
        );
    }
}
