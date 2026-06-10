<?php

namespace App\Domains\FileUpload\Controllers;

use App\Domains\FileUpload\Actions\ListWorkOrderFilesAction;
use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\FileUpload\Resources\WorkOrderFileResource;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListWorkOrderFilesController extends ApiController
{
    public function __construct(private readonly ListWorkOrderFilesAction $action) {}

    public function __invoke(WorkOrder $workOrder): JsonResponse
    {
        $this->authorize('viewAny', WorkOrderFile::class);

        $files = $this->action->execute($workOrder);

        return ApiResponse::success(
            data: WorkOrderFileResource::collection($files),
            message: 'Files retrieved successfully.',
        );
    }
}
