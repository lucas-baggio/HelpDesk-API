<?php

namespace App\Domains\FileUpload\DTOs;

use App\Domains\FileUpload\Requests\UploadWorkOrderFileRequest;
use Illuminate\Http\UploadedFile;

readonly class UploadWorkOrderFileData
{
    public function __construct(
        public string $workOrderId,
        public string $uploadedBy,
        public UploadedFile $file,
    ) {}

    public static function fromRequest(UploadWorkOrderFileRequest $request, string $workOrderId): self
    {
        return new self(
            workOrderId: $workOrderId,
            uploadedBy: $request->user()->id,
            file: $request->file('file'),
        );
    }
}
