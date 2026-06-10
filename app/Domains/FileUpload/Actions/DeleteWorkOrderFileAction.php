<?php

namespace App\Domains\FileUpload\Actions;

use App\Domains\FileUpload\Models\WorkOrderFile;
use Illuminate\Support\Facades\Storage;

class DeleteWorkOrderFileAction
{
    public function execute(WorkOrderFile $file): void
    {
        // RN-027: physical storage must be deleted alongside the record
        Storage::disk('local')->delete($file->file_path);

        $file->delete();
    }
}
