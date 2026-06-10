<?php

namespace App\Domains\WorkOrder\Controllers;

use App\Domains\WorkOrder\Actions\ListWorkOrdersAction;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Resources\WorkOrderResource;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListWorkOrdersController extends ApiController
{
    public function __construct(private readonly ListWorkOrdersAction $action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkOrder::class);

        $workOrders = $this->action->execute(
            ticketId: $request->query('ticket_id') ?: null,
            status: WorkOrderStatus::tryFrom($request->query('status', '')),
        );

        return ApiResponse::paginated(
            collection: WorkOrderResource::collection($workOrders),
            message: 'Work orders retrieved successfully.',
        );
    }
}
