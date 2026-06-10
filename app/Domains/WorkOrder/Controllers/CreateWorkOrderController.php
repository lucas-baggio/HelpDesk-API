<?php

namespace App\Domains\WorkOrder\Controllers;

use App\Domains\WorkOrder\Actions\CreateWorkOrderAction;
use App\Domains\WorkOrder\Requests\CreateWorkOrderRequest;
use App\Domains\WorkOrder\Resources\WorkOrderResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateWorkOrderController extends ApiController
{
    public function __construct(private readonly CreateWorkOrderAction $action) {}

    public function __invoke(CreateWorkOrderRequest $request): JsonResponse
    {
        $workOrder = $this->action->execute($request->toCreateWorkOrderData());

        return ApiResponse::created(
            data: new WorkOrderResource($workOrder),
            message: 'Work order created successfully.',
        );
    }
}
