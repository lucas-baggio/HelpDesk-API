<?php

namespace App\Domains\FileUpload\Controllers;

use App\Domains\FileUpload\Actions\DeleteWorkOrderFileAction;
use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class DeleteWorkOrderFileController extends ApiController
{
    public function __construct(private readonly DeleteWorkOrderFileAction $action) {}

    public function __invoke(WorkOrder $workOrder, WorkOrderFile $file): JsonResponse
    {
        $this->authorize('delete', $file);

        $this->action->execute($file);

        return ApiResponse::deleted(message: 'File deleted successfully.');
    }
}
