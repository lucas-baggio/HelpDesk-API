<?php

namespace App\Domains\FileUpload\Actions;

use App\Domains\FileUpload\DTOs\UploadWorkOrderFileData;
use App\Domains\FileUpload\Models\WorkOrderFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadWorkOrderFileAction
{
    public function execute(UploadWorkOrderFileData $data): WorkOrderFile
    {
        return DB::transaction(function () use ($data): WorkOrderFile {
            $file = $data->file;
            $storagePath = 'work-order-files/' . $data->workOrderId;
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Store the file — RN-029: validated before reaching here
            $path = Storage::disk('local')->putFileAs($storagePath, $file, $fileName);

            return WorkOrderFile::query()->create([
                'work_order_id' => $data->workOrderId,
                'uploaded_by' => $data->uploadedBy,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'created_at' => now(),
            ]);
        });
    }
}
