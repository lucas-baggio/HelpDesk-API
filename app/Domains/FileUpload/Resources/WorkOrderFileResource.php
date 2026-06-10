<?php

namespace App\Domains\FileUpload\Resources;

use App\Shared\Http\Resources\BaseJsonResource;
use Illuminate\Http\Request;

class WorkOrderFileResource extends BaseJsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_order_id' => $this->work_order_id,
            'uploaded_by' => $this->uploaded_by,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'url' => route('work-order-files.download', ['work_order' => $this->work_order_id, 'file' => $this->id]),
            'created_at' => $this->created_at,
        ];
    }
}
