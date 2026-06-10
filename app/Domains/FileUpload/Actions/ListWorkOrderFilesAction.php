<?php

namespace App\Domains\FileUpload\Actions;

use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Eloquent\Collection;

class ListWorkOrderFilesAction
{
    /**
     * @return Collection<int, WorkOrderFile>
     */
    public function execute(WorkOrder $workOrder): Collection
    {
        return $workOrder->files()->orderBy('created_at')->get();
    }
}
