<?php

namespace App\Domains\FileUpload\Requests;

use App\Domains\FileUpload\Models\WorkOrderFile;
use Illuminate\Foundation\Http\FormRequest;

class UploadWorkOrderFileRequest extends FormRequest
{
    /** RN-029: max 10 MB per file. */
    private const MAX_SIZE_KB = 10240;

    /** RN-026: permitted MIME types. */
    private const ALLOWED_MIMES = 'jpeg,jpg,png,gif,webp,pdf';

    public function authorize(): bool
    {
        return $this->user()?->can('create', WorkOrderFile::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:' . self::ALLOWED_MIMES,
                'max:' . self::MAX_SIZE_KB,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.mimes' => 'Only JPEG, PNG, GIF, WebP, and PDF files are allowed.',
            'file.max' => 'The file may not be larger than 10 MB.',
        ];
    }
}
