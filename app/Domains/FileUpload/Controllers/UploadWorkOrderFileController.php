<?php

namespace App\Domains\FileUpload\Controllers;

use App\Domains\FileUpload\Actions\UploadWorkOrderFileAction;
use App\Domains\FileUpload\DTOs\UploadWorkOrderFileData;
use App\Domains\FileUpload\Requests\UploadWorkOrderFileRequest;
use App\Domains\FileUpload\Resources\WorkOrderFileResource;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Http\ApiResponse;
use App\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UploadWorkOrderFileController extends ApiController
{
    public function __construct(private readonly UploadWorkOrderFileAction $action) {}

    public function __invoke(UploadWorkOrderFileRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $data = UploadWorkOrderFileData::fromRequest($request, $workOrder->id);

        $file = $this->action->execute($data);

        return ApiResponse::created(
            data: new WorkOrderFileResource($file),
            message: 'File uploaded successfully.',
        );
    }
}
