<?php

namespace App\Domains\FileUpload\Controllers;

use App\Domains\FileUpload\Actions\DownloadWorkOrderFileAction;
use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Http\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadWorkOrderFileController extends ApiController
{
    public function __construct(private readonly DownloadWorkOrderFileAction $action) {}

    public function __invoke(WorkOrder $workOrder, WorkOrderFile $file): BinaryFileResponse
    {
        $this->authorize('view', $file);

        $absolutePath = $this->action->execute($file);

        return response()->download($absolutePath, $file->file_name);
    }
}
