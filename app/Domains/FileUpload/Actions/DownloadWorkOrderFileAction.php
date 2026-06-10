<?php

namespace App\Domains\FileUpload\Actions;

use App\Domains\FileUpload\Models\WorkOrderFile;
use Illuminate\Support\Facades\Storage;

class DownloadWorkOrderFileAction
{
    public function execute(WorkOrderFile $file): string
    {
        return Storage::disk('local')->path($file->file_path);
    }
}
