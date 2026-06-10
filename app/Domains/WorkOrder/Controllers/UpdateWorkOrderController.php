<?php

namespace App\Domains\WorkOrder\Controllers;

use App\Domains\WorkOrder\Actions\UpdateWorkOrderAction;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Requests\UpdateWorkOrderRequest;
use App\Domains\WorkOrder\Resources\WorkOrderResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateWorkOrderController extends ApiController
{
    public function __construct(private readonly UpdateWorkOrderAction $action) {}

    public function __invoke(UpdateWorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $workOrder = $this->action->execute($workOrder, $request->toUpdateWorkOrderData());

        return ApiResponse::success(
            data: new WorkOrderResource($workOrder),
            message: 'Work order updated successfully.',
        );
    }
}
