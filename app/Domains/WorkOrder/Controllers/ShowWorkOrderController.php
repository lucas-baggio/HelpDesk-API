<?php

namespace App\Domains\WorkOrder\Controllers;

use App\Domains\WorkOrder\Actions\ShowWorkOrderAction;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Resources\WorkOrderResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ShowWorkOrderController extends ApiController
{
    public function __construct(private readonly ShowWorkOrderAction $action) {}

    public function __invoke(WorkOrder $workOrder): JsonResponse
    {
        $this->authorize('view', $workOrder);

        return ApiResponse::success(
            data: new WorkOrderResource($this->action->execute($workOrder)),
            message: 'Work order retrieved successfully.',
        );
    }
}
