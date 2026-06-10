<?php

namespace App\Domains\WorkOrder\Controllers;

use App\Domains\WorkOrder\Actions\StartWorkOrderAction;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Resources\WorkOrderResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class StartWorkOrderController extends ApiController
{
    public function __construct(private readonly StartWorkOrderAction $action) {}

    public function __invoke(WorkOrder $workOrder): JsonResponse
    {
        $this->authorize('changeStatus', $workOrder);

        $workOrder = $this->action->execute($workOrder);

        return ApiResponse::success(
            data: new WorkOrderResource($workOrder),
            message: 'Work order moved to in execution.',
        );
    }
}
